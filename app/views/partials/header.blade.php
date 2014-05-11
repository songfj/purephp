<header class="main-header" role="banner">
    <!-- Static navbar -->
    <div class="main-navbar navbar navbar-default navbar-static-top">
        <div class="main-nav-right">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand logo" href="<?php echo url() ?>">
                        <img height="50" width="50" src="<?php echo asset('img/logo.png'); ?>" alt=""/>
                    </a>
                </div>
                <nav role="navigation" class="navbar-collapse collapse">
                    <ul class="main-menu nav navbar-nav">
                        <li class="menu-item menu-item-3  active ">
                            <a href="<?php echo url() ?>">Home</a>
                        </li>
                        <li class="menu-item menu-item-4 ">
                            <a href="#">Contact</a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">ES <b class="caret"></b></a>
                            <ul class="dropdown-menu lang-list">
                                <li class=" active lang-es">
                                    <a title="Español" rel="alternate" hreflang="es" href="#">Español</a>
                                </li>
                                <li class=" lang-en">
                                    <a title="English" rel="alternate" hreflang="en" href="#">English</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <!--/.nav-collapse -->
            </div>
        </div>
    </div>
</header>