<?php include_once 'global_web.php';

 $enviroment == 'local'
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= trim($description); ?>">
    <meta name="author" content="ClimbX">
    <title><?= trim($title); ?></title>
    <?php if ($enviroment == 'local'): ?>
    <link href="http://localhost/smsxmail/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/smsxmail/assets/css/freelancer.css" rel="stylesheet">
        <script src="http://localhost/smsxmail/assets/jquery/angular.min.js"></script>

    <link href="http://localhost/smsxmail/assets/font_awesome/css/font-awesome.css" rel="stylesheet">
    <link href="http://localhost/smsxmail/assets/font_awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="http://localhost/smsxmail/assets/ionicons/css/ionicons.min.css" rel="stylesheet">
    <?php else: ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link href="css/freelancer.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.3/angular.js"></script>
    <?php endif; ?>
    <link href="https://cdnbachecubano.tk/img/bachecubano-favicon.min.png" rel="shortcut icon">
</head>
<body id="page-top" class="index" ng-app>
<!-- Navigation -->
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#page-top">smsXmail</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li class="hidden">
                    <a href="#page-top"></a>
                </li>
                <li class="page-scroll">
                    <a href="#about">Acerca</a>
                </li>
                <li class="page-scroll">
                    <a href="#sendsms">Enviar SMS</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<header>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <img class="img-responsive" src="img/profile.png" alt="">

                <div class="intro-text">
                    <span class="name"><?= trim($title); ?></span>
                    <hr class="star-light">
                    <span class="skills"><?= trim($description); ?></span>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- About Section -->
<section class="success" id="about">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2>Acerca</h2>
                <hr class="star-light">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-lg-offset-2">
                <p>smsXmail es un servicio destinado al envio de SMS hacia Cuba y cualquier parte del mundo.
                    Su uso es muy sencillo y barato, además puedes enviar sms desde tu propio correo.</p>
            </div>
            <div class="col-lg-4">
                <p>Con el mejor porciento de entrega, somos líderes en el envio de SMS personalizado y masivo a destinos
                    en Cuba y cualquier otro país, mejoramos nuestros precios constantemente!</p>
            </div>
            <div class="col-lg-8 col-lg-offset-2 text-center">
                <a href="#" class="btn btn-lg btn-outline">
                    <i class="fa fa-download"></i> Descargar aplicación
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="sendsms">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2>enviar SMS</h2>
                <hr class="star-primary">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <!-- To configure the contact form email address, go to mail/contact_me.php and update the email address in the PHP file on line 19. -->
                <!-- The form should work on most web servers, but if the form is not working you may need to configure your web server differently. -->
                <form name="sentMessage" id="contactForm" novalidate>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Su nombre</label>
                            <input type="text" class="form-control" placeholder="Su nombre" id="name" required
                                   data-validation-required-message="Please enter your name.">

                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Su correo</label>
                            <input type="email" class="form-control" placeholder="Su dirección de correo" id="email"
                                   required
                                   data-validation-required-message="Escriba aquí su dirección de correo.">

                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Teléfono de destino</label>
                            <input type="tel" class="form-control" placeholder="Teléfono de destino" id="phone" required
                                   data-validation-required-message="Por favor escriba el celular a enviar el SMS.">

                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Mensaje</label>
                            <textarea rows="5" class="form-control" placeholder="Mensaje" id="message" required
                                      data-validation-required-message="Escriba aquí el SMS."
                                      maxlength="159"></textarea>

                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <br>

                    <div id="success"></div>
                    <div class="row">
                        <div class="form-group col-xs-12">
                            <button type="submit" class="btn btn-success btn-lg">Enviar SMS</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="text-center">
    <div class="footer-above">
        <div class="container">
            <div class="row">
                <div class="footer-col col-md-4">
                    <h3>Ubicación</h3>

                    <p>        <br>Altahabana, Boyeros, CU 10800</p>
                </div>
                <div class="footer-col col-md-4">
                    <h3>Estamos en la Web</h3>
                    <ul class="list-inline">
                        <li>
                            <a href="#" class="btn-social btn-outline"><i class="fa fa-fw fa-facebook"></i></a>
                        </li>
                        <li>
                            <a href="#" class="btn-social btn-outline"><i class="fa fa-fw fa-google-plus"></i></a>
                        </li>
                        <li>
                            <a href="#" class="btn-social btn-outline"><i class="fa fa-fw fa-twitter"></i></a>
                        </li>
                        <li>
                            <a href="#" class="btn-social btn-outline"><i class="fa fa-fw fa-linkedin"></i></a>
                        </li>
                        <li>
                            <a href="#" class="btn-social btn-outline"><i class="fa fa-fw fa-dribbble"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="footer-col col-md-4">
                    <h3>About Freelancer</h3>

                    <p>Freelance is a free to use, open source Bootstrap theme created by <a
                            href="http://startbootstrap.com">Start Bootstrap</a>.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-below">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    Copyright &copy; Your Website 2014
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button (Only visible on small and extra-small screen sizes) -->
<div class="scroll-top page-scroll visible-xs visble-sm">
    <a class="btn btn-primary" href="#page-top">
        <i class="fa fa-chevron-up"></i>
    </a>
</div>
<?php if ($enviroment == 'local'): ?>
    <script src="http://localhost/smsxmail/assets/jquery/jquery.min.js"></script>
    <script src="http://localhost/smsxmail/assets/bootstrap/bootstrap.min.js"></script>
    <script src="js/classie.js"></script>
    <script src="js/cbpAnimatedHeader.js"></script>
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>
    <script src="js/freelancer.js"></script>
<?php else: ?>
    <script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="https://cdnjs.com/libraries/angular.js/"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/classie.js"></script>
    <script src="js/cbpAnimatedHeader.js"></script>
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>
    <script src="js/freelancer.js"></script>
<?php endif; ?>
</body>
</html>
