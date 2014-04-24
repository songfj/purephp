<?php
$ds = DIRECTORY_SEPARATOR;
$rootpath = realpath(dirname(__FILE__)) . $ds;
$parentpath = realpath(dirname($rootpath)) . $ds;
$purepath = $rootpath . 'src' . $ds;

require_once $purepath . 'pure' . $ds . 'str.php';
require_once $purepath . 'pure' . $ds . 'fs.php';

$is_post = isset($_POST['project_name']);
$project_created = false;
$errors = array();

if ($is_post) {
    $project_name = pure_str::slugize($_POST['project_name'], '_');
    if (empty($project_name)) {
        $errors[] = 'The project name cannot be empty';
    }
    if (!is_writable($parentpath)) {
        $errors[] = 'The parent path is not writable: <var>' . $parentpath . '</var>';
    }

    if (empty($errors)) {
        $project_new_path = $parentpath . $project_name . DIRECTORY_SEPARATOR;
        if (is_dir($project_new_path)) {
            $errors[] = 'A project with the same name is found under <var>' . $project_new_path . '</var>';
        }

        if (empty($errors)) {

            mkdir($project_new_path, 0755);

            try {
                pure_fs::copy($rootpath . 'demo' . $ds, $project_new_path);
                pure_fs::copy($rootpath, $project_new_path . 'app' . $ds . 'vendor' . $ds . 'purephp');
            } catch (Exception $exc) {
                $errors[] = $exc->getTraceAsString();
            }

            if (empty($errors)) {
                $project_created = true;
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>PurePHP Framework</title>
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
        <style>
            /* Space out content a bit */
            body {
                padding-top: 20px;
                padding-bottom: 20px;
            }

            /* Everything but the jumbotron gets side spacing for mobile first views */
            .header,
            .marketing,
            .footer {
                padding-left: 15px;
                padding-right: 15px;
            }

            /* Custom page header */
            .header {
                border-bottom: 1px solid #e5e5e5;
            }
            /* Make the masthead heading the same height as the navigation */
            .header h3 {
                margin-top: 0;
                margin-bottom: 0;
                line-height: 40px;
                padding-bottom: 19px;
            }

            /* Custom page footer */
            .footer {
                padding-top: 19px;
                color: #777;
                border-top: 1px solid #e5e5e5;
            }

            /* Customize container */
            @media (min-width: 768px) {
                .container {
                    max-width: 730px;
                }
            }
            .container-narrow > hr {
                margin: 30px 0;
            }

            /* Main marketing message and sign up button */
            .jumbotron {
                text-align: center;
                border-bottom: 1px solid #e5e5e5;
            }

            /* Supporting marketing content */
            .marketing {
                margin: 40px 0;
            }
            .marketing p + h4 {
                margin-top: 28px;
            }

            /* Responsive: Portrait tablets and up */
            @media screen and (min-width: 768px) {
                /* Remove the padding we set earlier */
                .header,
                .marketing,
                .footer {
                    padding-left: 0;
                    padding-right: 0;
                }
                /* Space out the masthead */
                .header {
                    margin-bottom: 30px;
                }
                /* Remove the bottom border on the jumbotron for visual effect */
                .jumbotron {
                    border-bottom: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h3 class="text-muted">PurePHP Framework</h3>
            </div>

            <?php if (empty($errors) and ($project_created === true)): ?>
                <div class="alert alert-success">
                    The project <b><?php echo $project_name ?></b> has been created successfully.
                    <a target="_blank" href="../<?php echo $project_name ?>/">Visit project now</a>
                </div>
            <?php endif; ?>

            <div class="jumbotron">
                <h1>New project</h1>
                <p class="lead">
                    Fill the form bellow to set a name to this project and setup the necessary folder structure.
                </p>
                <form action="install.php" method="post">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="input-group">
                                <input type="text" name="project_name" placeholder="Project name" 
                                       value="<?php echo isset($_POST['project_name']) ? $_POST['project_name'] : '';  ?>" class="form-control input-lg">
                                <span class="input-group-btn">
                                    <button class="btn btn-lg btn-success" type="submit">Create project</button>
                                </span>
                            </div><!-- /input-group -->
                        </div><!-- /.col-lg-12 -->
                    </div><!-- /.row -->
                </form>
            </div>

            <?php if (!empty($errors)): foreach ($errors as $i => $e): ?>
                    <div class="alert alert-danger">
                        <?php print_r($e); ?>
                    </div>
                <?php endforeach;
            endif;
            ?>
        </div> <!-- /container -->

        <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    </body>
</html>