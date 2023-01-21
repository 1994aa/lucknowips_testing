            <?php
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                session_start();
            }

            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if (!isset($no_url)) {
                $_SESSION['redirect_url'] = $actual_link;
            }
            ?>
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/admin/admin_default.php" style="padding-top:7px;"><?php echo (SITE_HEADER_TEXT == '' ? '<img src="/images/added_logo.png" />' : SITE_HEADER_TEXT); ?></a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <a class="btn btn-primary" href="/admin/change_session.php">
                    <strong>
                        <?php
                        if ($_SESSION['DB'] == 'addedschools_lucknowips_testing-23-24') {
                            echo 'Session 23 - 24';
                        } elseif ($_SESSION['DB'] == 'addedschools_lucknowips_testing-22-23') {
                            echo 'Session 22 - 23';
                        } else if ($_SESSION['DB'] == 'addedschools_lucknowips_testing-21-22') {
                            echo 'Session 21 - 22';
                        } else {
                            echo 'Before 21 - 22';
                        }
                        ?>
                    </strong>
                </a>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <div class="navbar-login">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <p class="text-center">
                                            <!--<img class="icon-img" src="../../site_images/school_logo/school_logo.png" alt="School Logo">-->
                                            <img class="icon-img" src="<?php echo SITE_HTTP_PATH . '/site_images/school_logo/school_logo.png'; ?>" alt="School Logo">
                                        </p>
                                    </div>
                                    <div class="col-lg-8">
                                        <p class="text-left" style=""><strong>LUCKNOW INTERNATIONAL PUBLIC SCHOOL</strong></p>
                                        <p class="text-left small"><strong>Faculty</strong><br /><?php echo $LoggedUser->GetUserName(); ?></p>
                                        <p class="text-left">
                                            <a href="#" class="btn btn-info btn-block btn-sm"><i class="fa fa-edit"></i>&nbsp;Edit Profile</a>
                                            <a href="/admin/change_password.php" class="btn btn-warning btn-block btn-sm"><i class="fa fa-key"></i>&nbsp;Change Password</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="navbar-login navbar-login-session">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p><a href="/logout.php?Admin=1" class="btn btn-danger btn-block"><i class="fa fa-sign-out fa-fw"></i>&nbsp;Logout</a></p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->