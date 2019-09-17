<?php
use Respect\Validation\Validator as V;

/**
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $message
 * @property string $user_ip
 * @property string $user_agent
 * @property string $uploaded_file
 * @property integer $created_at
 *
 * Class Messages
 */
class Messages
{
    private $db;
    private $file_name         = null;
    private $google_api_url    = 'https://www.google.com/recaptcha/api/siteverify';
    private $captcha_secret    = '6LdS0rgUAAAAAHq8izLJa_0PlMMd06pQJA4VC7nZ';
    private $errors            = [
        'name_error'      => 'Укажите имя (не менее 3-х символов)',
        'email_error'     => 'Некорректный формат email',
        'file_error'      => 'Неподдерживаемый формат',
        'captcha_error'   => 'Пройдите проверку',
        'captcha_robot'   => 'Вы робот!',
        'file_size_error' => 'Большой размер файла',
        'message_error'   => 'Длина сообщения не менее 25-ти символов',
    ];
    private $supported_formats = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'text/plain',
        'text/html',
    ];


    /**
     * Messages constructor.
     */
    public function __construct()
    {
        $this->db = Db::getConnection();
    }

    /**
     * @param $post
     * @param $files
     * @return array
     */
    public function validate($post, $files)
    {
        $errors = [];

        $v_name    = V::stringType();
        $v_email   = V::email();
        $v_message = V::stringType();
        $v_captcha = V::notBlank();

        $valid_email   = $v_email->validate($post['email']);
        $valid_name    = $v_name->length(3)->validate($post['username']);
        $valid_captcha = $v_captcha->validate($post['g-recaptcha-response']);
        $valid_message = $v_message->length(25)->validate($post['message']);

        if (!$valid_name)    $errors['username']    = $this->errors['name_error'];
        if (!$valid_email)   $errors['email']       = $this->errors['email_error'];
        if (!$valid_message) $errors['message']     = $this->errors['message_error'];
        if (!$valid_captcha) $errors['captcha']     = $this->errors['captcha_error'];
        else {
            $query =
                $this->google_api_url . '?secret=' . $this->captcha_secret . '&response=' .
                $_POST['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR'];

            $data = json_decode(file_get_contents($query));
            if (!$data->success) {
                $errors['captcha'] = $this->errors['captcha_robot'];
            }
        }

        if (!empty($files['file']['name'])) {
            $finfo     = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $files['file']['tmp_name']);

            if (!in_array($file_type, $this->supported_formats)) {
                $errors['file'] = $this->errors['file_error'];
            }
            if (in_array($file_type, ["text/plain"]) && $files['file']['size'] > MAX_FILE_BYTES) {
                $errors['file'] = $this->errors['file_size_error'];
            }
        }

        return $errors;
    }

    /**
     * @param $data
     * @return bool
     */
    public function addMessage($data)
    {
        $sql = 'INSERT INTO messages (username, email, message, user_ip, user_agent, uploaded_file, created_at) 
                VALUES (:username, :email, :message, :user_ip, :user_agent, :uploaded_file, :created_at)';

        $result = $this->db->prepare($sql);
        $result->bindValue(':username',      $data['username'],    PDO::PARAM_STR);
        $result->bindValue(':email',         $data['email'],       PDO::PARAM_STR);
        $result->bindValue(':message',       $data['message'],     PDO::PARAM_STR);
        $result->bindValue(':created_at',    time(),               PDO::PARAM_INT);
        $result->bindValue(':user_ip',       $this->getUserIp(),   PDO::PARAM_STR);
        $result->bindValue(':user_agent',    $this->getUserAgent(),PDO::PARAM_STR);
        $result->bindValue(':uploaded_file', $this->file_name,     PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * @return bool|null
     */
    public function upload()
    {
        $res = move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR . $_FILES['file']['name']);
        if ($res) {
            $this->file_name = $_FILES['file']['name'];
            return $this->file_name;
        }
        return false;
    }

    /**
     * @param int $page
     * @return array
     */
    public static function getAllMessages($page = 1)
    {
        $messages = [];
        $page     = intval($page);
        $offset   = ($page - 1) * SHOW_BY_DEFAULT;
        $db       = Db::getConnection();

        $result = $db->query(
                    'SELECT * FROM messages ORDER BY created_at DESC LIMIT '
                             . SHOW_BY_DEFAULT . ' OFFSET ' .$offset);

        $messages = $result->fetchAll();
        return $messages;
    }

    /**
     * @return mixed
     */
    public static function getCountMessages()
    {
        $messages = [];
        $db       = Db::getConnection();
        $result   = $db->query('SELECT count(id) FROM messages');
        $messages = $result->fetch();

        return $messages['count(id)'];
    }

    /**
     * Заменяет "опасные" символы
     * @param $post
     * @return mixed
     */
    public static function encodeChars($post)
    {
        array_walk($post, function (&$item) {
            $item = htmlspecialchars($item);
        });
        return $post;
    }

    /**
     * @return mixed|null
     */
    protected function getUserIp()
    {
        $ip = null;
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * @return mixed|null
     */
    protected function getUserAgent()
    {
        $agent = null;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        }
        return $agent;
    }
}