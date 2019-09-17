<?php

class SiteController
{
    public function actionIndex($page = 1)
    {
        $errors         = [];
        $all_messages   = Messages::getAllMessages($page);
        $count_messages = Messages::getCountMessages();
        $pagination     = new Pagination($count_messages, $page, SHOW_BY_DEFAULT, 'page-');

        if (isset($_POST['submit'])) {
            $model  = new Messages();
            $errors = $model->validate($_POST, $_FILES);

            if (empty($errors)) {

                if (!empty($_FILES['file']['name'])) {

                    // Изменение размеров файла
                    $file_name = $model->upload();
                    if ($file_name && pathinfo($file_name, PATHINFO_EXTENSION) != 'txt') {
                        $image = new ImageMaker();
                        $image->load(UPLOAD_DIR . $file_name);
                        $image->resize(320, 240);
                        $image->save(UPLOAD_DIR . 'small-'.$file_name);
                    }
                }

                $post = Messages::encodeChars($_POST);
                $model->addMessage($post);
                header("Location: /");
            }
        }

        require_once(ROOT . '/views/main/index.php');
        return true;
    }
}