<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Morph API Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-8 col-sm-10 col-xs-10">
                <div class="card shadow-lg mb-5 bg-body-tertiary w-100 pt-5" style="width: 18rem;">
                    <img src="<?= $user['avatar']; ?>" class="img-thumbnail rounded-circle" alt="..." width="100" height="auto" style="margin: 0 auto;" />
                    <div class="card-body">
                        <h5>Attributes</h5>
                        <p class="card-text">
                            <ul>
                                <?php foreach ($user as $name => $value) { ?>
                                    <?php if ('region' != $name && 'language' != $name) { ?>
                                        <li><strong><?= yii\helpers\Html::encode($name); ?></strong>：<?= yii\helpers\Html::encode($value); ?></li>
                                    <?php } else { ?>
                                        <li>
                                            <h6><?= yii\helpers\Html::encode($name); ?></h6>
                                            <ul>
                                                <?php foreach ($value as $k => $v) { ?>
                                                    <li><strong><?= yii\helpers\Html::encode($k); ?>：<?= yii\helpers\Html::encode($v); ?></li>
                                                <?php } ?>
                                            </ul>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </p>
                    </div>
                    <div class="card-footer">
                        <h5>AccessToken</h5>
                        <?= yii\helpers\Html::encode($accessToken); ?>
                        <h5 class="mt-3">Media-Oauth</h5>
                        <div>
                            <a href="<?= yii\helpers\Url::toRoute(['media-oauth/meta', 'redirectUrl' => yii\helpers\Url::toRoute('/'), 'accessToken' => $accessToken], true); ?>" class="btn btn-primary btn-xs" target="_blank">Meta</a> | <a href="<?= yii\helpers\Url::toRoute(['media-oauth/googleads', 'redirectUrl' => yii\helpers\Url::toRoute('/'), 'accessToken' => $accessToken], true); ?>" class="btn btn-primary btn-xs" target="_blank">GoogleAds</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>