<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$GLOBALS['ADMIN_ROLE_REQUIRED'] = false;

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
?>
<!DOCTYPE html> <?php $TITLE = 'pd@min'; $SUB_TITLE='Postfix-Dovecot Admin' ?>
<!--[if lt IE 10]>     <html lang="de" ng-app="app" class="unsupported-browser"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="de" ng-app="app"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $protocoll = isset($_SERVER["REQUEST_SCHEME"]) ? $_SERVER["REQUEST_SCHEME"] : 'http'; ?>
    <base href="<?=$protocoll."://".$_SERVER["HTTP_HOST"].dirname($_SERVER["SCRIPT_NAME"])?>/" />

    <title>pd@min</title>
    <meta name="description" content="pd@min == postfix- and dovecot-admin">
    <meta name="author" content="hofrichter.net">
    <link rel="icon" href="/favicon.ico">
    
    <link href="res/css/waiting-for-angular.css" rel="stylesheet" type="text/css">

    <script src="res/3rd/angular/1.4.5/angular.min.js"></script>
    <script src="res/3rd/angular/1.4.5/angular-animate.min.js"></script>
    <script src="res/3rd/angular/1.4.5/angular-route.min.js" type="text/javascript"></script>
    <script src="res/3rd/angular/1.4.5/angular-resource.min.js" type="text/javascript"></script>
    <script src="res/3rd/angular/1.4.5/angular-cookies.min.js" type="text/javascript"></script>
    <script src="res/3rd/angular/1.4.5/angular-sanitize.min.js" type="text/javascript"></script>
    <script src="res/3rd/angular-ui/ui-bootstrap-tpls-0.13.4.min.js"></script>
    <script src="res/3rd/angular-extensions/http-auth-interceptor.js"></script>
    
    <script src="res/3rd/jssha/sha.js"></script>

    <link href="res/3rd/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="res/css/styles.css" rel="stylesheet" type="text/css">
    <script src="res/js/array.js" type="text/javascript"></script>

    <script data-src="res/ng/i18n/i18n-de.json" type="text/javascript"></script>
    <script src="res/ng/i18n.js" type="text/javascript"></script>
    <script src="res/ng/storage.js" type="text/javascript"></script>
    <script src="res/ng/session.js" type="text/javascript"></script>
    <script src="res/ng/custom-directives.js" type="text/javascript"></script>
    <script src="res/ng/http-status-interceptor.js" type="text/javascript"></script>
    <script src="res/ng/modalService.js" type="text/javascript"></script>
    
    <script type="text/javascript">
        var modules = ['ngRoute', 'ngCookies', 'ngAnimate', 'ngSanitize', 'ui.bootstrap', 'storage', 'http-status-interceptor', 'custom-directives', 'i18n', 'modalService'];
        var DEPLOY_INTERVAL = '<?=DEPLOY_INTERVAL?>';
    </script>
    <?php
        $d = dirname(__DIR__);
        $skip = basename(__DIR__);
        $items = scandir($d);
        foreach ($items as $i) {
            if ($i != '..' && $i != '.' && $i != $skip && is_dir("$d/$i") && file_exists("$d/$i/$i.js")) {
                printf('<script src="views/%s/%s.js" type="text/javascript"></script>%s', $i, $i, "\n");
                printf('<script type="text/javascript">modules.push("%s");</script>%s', $i, "\n");
            }
        }
    ?>
    <script src="views/index/index.js" type="text/javascript"></script>

</head>
<body class="waiting-for-angular" ng-class="viewId" ng-init="sessionIsValid = true">
    <div class="http-status spinner">
        {{ 'http-status.method.' + httpstatus.method | i18n }} {{ httpstatus.url }}
    </div>
    <form name="form">
        <div id="initializing-panel"></div>

        <!-- template: http://getbootstrap.com/examples/dashboard/ -->
        <nav class="navbar navbar-inverse navbar-fixed-top" ng-init="showMobileNav=true">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button ng-click="showMobileNav = !showMobileNav" type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">{{ 'navigation.toggle' | i18n }}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#/home">
                        <span>p</span><span class="navbar-brand-label">ostfix- and&nbsp;</span>
                        <span>d</span><span class="navbar-brand-label">ovecot-</span>
                        <span class="navbar-brand-label-inverse">@</span><span class="navbar-brand-label">ad</span><span>min</span>
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse" collapse="showMobileNav">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a ng-hide="!sessionIsValid" href="javascript:void(0)" ng-controller="LogoutCtrl" ng-click="logout()"><span class="glyphicon glyphicon-off"></span> <span>{{ 'menu.logout' | i18n }}</span></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div ng-hide="!sessionIsValid" class="container-fluid">
            <div class="row" style="margin:0" ng-show="isAdmin">
                <div class="col-sm-3 col-xs-1 sidebar-placeholder" style="height:1px; float:left">
                    &nbsp;
                    <!-- placeholder for the sidebar width, because sidebar is fixed-position -->
                </div>
                <div messages class="col-sm-9 col-xs-11"></div>
            </div>
            <div class="row">
                <div class="col-sm-3 sidebar" ng-show="isAdmin">
                    <ul class="nav nav-sidebar" mark-active="active">
                    <li style="float:none; clear:both;"><a href="#/home"><span class="glyphicon glyphicon-home"></span> <span>{{ 'menu.home' | i18n }}</span></a></li>
                    <li><a href="#/domains"><span class="glyphicon glyphicon-globe"></span> <span>{{ 'menu.domains' | i18n }}</span></a></li>
                    <li><a href="#/accounts"><span class="glyphicon glyphicon-user"></span> <span>{{ 'menu.accounts' | i18n }}</span></a></li>
                    <li><a href="#/addresses"><span class="glyphicon glyphicon-envelope"></span> <span>{{ 'menu.addresses' | i18n }}</span></a></li>
                    <li><a href="#/tests"><span class="glyphicon glyphicon-fire"></span> <span>{{ 'menu.tests' | i18n }}</span></a></li>
                    <li><a href="#/history"><span class="glyphicon glyphicon-time"></span> <span>{{ 'menu.history' | i18n }}</span></a></li>
                    </ul>
                </div>
                <div class="col-sm-3 col-xs-1 sidebar-placeholder" ng-show="isAdmin">
                    &nbsp;
                    <!-- placeholder for the sidebar width, because sidebar is fixed-position -->
                </div>
                <div id="ng-view" class="main {{isAdmin ? 'col-sm-9 col-xs-11' : ''}} {{viewId}}">
                    <div class="headline">{{'headline.' + viewId | i18n}}</div>
                    <div ng-view></div>
                </div>
                <!--
                <div ng-view id="ng-view" class="main {{isAdmin ? 'col-sm-9 col-xs-11' : ''}} {{viewId}}"></div>
                -->
            </div>
        </div>
    </form>
</body>
</html>
<?php } ?>