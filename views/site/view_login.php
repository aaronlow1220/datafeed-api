<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Morph API Login/Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card shadow-lg mb-5 bg-body-tertiary" style="width: 18rem;">
                    <img src="https://picsum.photos/400/300" class="card-img-top" alt="..." width="100%" height="auto"
                        border="0" />
                    <div class="card-body">
                        <h5 class="card-title text-center pt-1 pb-3">Login/Registration</h5>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-danger btn-sm" href="<?= $googleLoginUrl; ?>"><i
                                    class="bi bi-google"></i> Google</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>