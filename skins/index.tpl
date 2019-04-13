<!DOCTYPE html>
<html lang="<?= hc(Config::$LANGUAGE['lang']); ?>" prefix="og: http://ogp.me/ns#">
<head>
  <meta charset="UTF-8">
    <?php foreach (Core::$META['dns-prefetch'] as $v) { ?>
      <link rel="dns-prefetch" href="<?= $v; ?>">
    <?php } ?>
  <title><?= hc(Core::$META['title']); ?></title>
  <meta name="apple-mobile-web-app-title" content="<?= hc(Core::$META['title']); ?>">
  <meta name="description" content="<?= hc(Core::$META['description']); ?>">
  <meta name="keywords" content="<?= hc(Core::$META['keywords']); ?>">
  <meta name="robots" content="index, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <meta property="og:title" content="<?= hc(Core::$META['title']); ?>">
  <meta property="og:locale" content="<?= hc(Config::$LANGUAGE['html_locale']); ?>">
  <meta property="og:site_name" content="<?= hc(Config::$SITENAME); ?>">
  <meta property="og:type" content="<?= ($_GET['_module'] == 'articles' ? 'article' : 'website'); ?>">
  <meta property="og:url" content="<?= hc(Config::$DOMAIN); ?><?= $_SERVER['REQUEST_URI']; ?>">
  <meta property="og:image" content="<?= hc(Config::$DOMAIN); ?>/logo.png">
  <meta property="og:description" content="<?= hc(Core::$META['description']); ?>">

    <?php if (!empty(Core::$META['prev'])) { ?>
      <link rel="prev" href="<?php echo Core::$META['prev']; ?>">
    <?php } ?>
    <?php if (!empty(Core::$META['next'])) { ?>
      <link rel="next" href="<?php echo Core::$META['next']; ?>">
    <?php } ?>
    <?php if (!empty(Core::$META['canonical'])) { ?>
      <link rel="canonical" href="<?php echo Core::$META['canonical']; ?>">
    <?php } ?>
    <?php if (!empty(Core::$META['shortlink'])) { ?>
      <link rel="shortlink" href="<?php echo Core::$META['shortlink']; ?>">
    <?php } ?>

  <link rel="icon" type="image/png" href="/logo.png" sizes="128x128">
  <meta name="application-name" content="<?= hc(Config::$SITENAME); ?>">

  <!-- for test -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
        integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
          integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
          crossorigin="anonymous"></script>

  <link href="/skins/css/general.min.css" rel="stylesheet">
    <?= Core::$HEAD; ?>

</head>

<body data-xsrf="<?= (isset($_SESSION['antixsrf']) ? $_SESSION['antixsrf'] : 'no'); ?>">
<header class="container">
  <div class="header-top">
    <div class="top-menu clearfix">
        <?php if (empty($_GET['route']) && $_GET['_module'] == 'main') { ?>
          <div class="top-logo float-left">
            <img src="/logo.png" alt="<?= hc(Config::$SITENAME); ?>">
          </div>
        <?php } else { ?>
          <a href="/" class="top-logo float-left">
            <img src="/logo.png" alt="<?= hc(Config::$SITENAME); ?>">
          </a>
        <?php }; ?>
      <div class="site-name float-left"><?= hc(Config::$SITENAME); ?></div>
      <div class="profile float-right">
          <?php if (!empty(\User::$id)) { ?>
            <div>
              <span><?= hc(\User::$name . ' (' . \User::$email . ') '); ?></span>
              <a href="/login/exit" class="btn btn-primary">Exit</a>
            </div>
          <?php } else { ?>
            <div>
              <span class="btn btn-success" data-toggle="modal" data-target="#authModal">Login</span>
            </div>
          <?php }; ?>
      </div>
    </div>
  </div>
</header>

<main class="container"><?= $content; ?></main>

<footer class="container">
  <div class="year">© 2019</div>
</footer>

<script src="/skins/js/general.js"></script>
<?php echo Core::$END; ?>

<div class="modal fade" id="authModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="authModalLabel">Login</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="authModalBody">
        <div class="error"></div>
        <form action="" method="post" id="authForm" data-ajax="1">
          <div class="form-group row" data-role="restore">
            <div class="col">We will send a link to reset your password</div>
          </div>
          <div class="form-group row" data-role="register">
            <label for="name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
              <input name="name" type="text" class="form-control" id="name" placeholder="Name">
              <div class="error"></div>
            </div>
          </div>
          <div class="form-group row" data-role="login register restore">
            <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
              <input name="email" type="text" class="form-control" id="email" placeholder="email@example.com" value="">
              <div class="error"></div>
            </div>
          </div>
          <div class="form-group row" data-role="login register">
            <label for="password" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
              <input name="password" type="password" class="form-control" id="password" placeholder="Password">
              <div class="error"></div>
            </div>
          </div>
          <div class="form-group row">
            <div class="col text-center">
              <button type="button" class="btn btn-primary submit" id="authSave">Login</button>
              <button type="button" class="btn btn-link authAction" id="addAuthAction" data-action="restore"
                      data-role="login">Forgot your password?
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <span id="authText">No account yet? </span>
        <button type="button" class="btn btn-link authAction" id="authAction" data-action="register">Register</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>