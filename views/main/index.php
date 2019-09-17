<?php
require_once(ROOT . '/views/layouts/header.php');

/* @var $errors /controllers/SiteController */
/* @var $pagination /controllers/SiteController */
/* @var $all_messages /controllers/SiteController */

?>

<main class="main-container">
    <section class="comment-form">
        <div class="container">
            <h2 class="visually-hidden">Новый комментарий</h2>
            <form action="/" enctype="multipart/form-data" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <p class="comment-form__row">
                            <label class="page-label" for="comment-form__name">Имя</label>
                            <input class="page-input" id="comment-form__name" name="username" required type="text">
                            <span class="page-error"><?= checkErrors($errors, 'username'); ?></span>
                        </p>

                        <p class="comment-form__row">
                            <label class="page-label" for="comment-form__email">E-mail</label>
                            <input class="page-input" id="comment-form__email" name="email" required type="text">
                            <span class="page-error"><?= checkErrors($errors, 'email'); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6 col-md-offset-2">
                        <p class="comment-form__row">
                            <label class="page-label" for="comment-form__message">Комментарий</label>
                            <textarea class="page-input page-textarea" id="comment-form__message" name="message"
                                      required></textarea>
                            <span class="page-error"><?= checkErrors($errors, 'message'); ?></span>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-md-offset-8">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="upload-file">
                                    <label class="label page-btn">
                                        <span class="title">Добавить файл</span>
                                        <input type="file" name="file">
                                    </label>
                                    <span class="page-error"><?= checkErrors($errors, 'file'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <button name="submit" class="page-btn comment-form__btn" type="submit">Записать</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-md-offset-11">
                        <div class="g-recaptcha" data-sitekey="6LdS0rgUAAAAABUGt2qLW6sPGoBqYSqPpNIvvDfh"></div>
                        <span class="page-error"><?= checkErrors($errors, 'captcha'); ?></span>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="comments">
        <div class="container">
            <div class="row">
                <div class="col-md-14">
                    <?php if (!empty($all_messages)): ?>
                        <h2 class="comments-title">Последние сообщения</h2>
                        <table class="table">
                            <thead class="thead-inverse">
                            <tr>
                                <th class="table-username">Username</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>File</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($all_messages as $message): ?>
                                <tr>
                                    <td class="table-ac">
                                        <?= mb_strimwidth($message['username'],0,15,'...') ?>
                                    </td>
                                    <td class="table-ac">
                                        <?= mb_strimwidth($message['email'],0,50,'...') ?>
                                    </td>
                                    <td>
                                        <?= mb_strimwidth($message['message'],0,200,'...') ?>
                                    </td>
                                    <td class="file-download">
                                        <?php if (pathinfo($message['uploaded_file'], PATHINFO_EXTENSION) === 'txt'): ?>
                                            <a href="template/uploads/<?= $message['uploaded_file'] ?>" download>Скачать</a>
                                        <?php else: ?>
                                            <a class="fancybox" rel="group" href="template/uploads/<?= $message['uploaded_file'] ?>">
                                                <img class="fancybox-img" src="template/uploads/small-<?= $message['uploaded_file'] ?>">
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <h2 class="comments-title">Оставьте сообщение первым!</h2>
                    <?php endif; ?>
                </div>
            </div>
            <?= $pagination->get() ?>
        </div>
    </section>
</main>

<?php require_once(ROOT . '/views/layouts/footer.php') ?>