<section class="bg-black">
    <div class="container-fluid px-5">
        <a href="<?= base_url(''); ?>auth/" class="btn-back-green">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                class="bi bi-chevron-left" viewBox="0 0 18 16">
                <path fill-rule="evenodd"
                    d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
            </svg>
        </a>
        <div class="row gx-5 align-items-center justify-content-center justify-content-lg-between">
            <div class="col-12">
                <div class="box-form-rpw">
                    <div class="col-12">
                        <?php if (isset($_SESSION["failed"])) { ?>
                        <div class="alert alert-warning" role="alert">
                            <?= @$_SESSION["success"] ?>
                        </div>
                        <?php } ?>

                        <?php if (isset($_SESSION["success"])) { ?>
                        <div class="alert alert-info" role="alert">
                            <?= @$_SESSION["success"] ?>
                        </div>
                        <?php } ?>
                    </div>
                    <form method="POST" action="<?= base_url('email/updatepassword'); ?>" autocomplete="off"
                        autocapitalize="none">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="row">
                                    <div class="col">
                                        <input type="text" name="anonemail" value="<?= $email ?>" hidden>
                                        <input type="text" name="gmail" value="<?= $gmail ?>" hidden>
                                        <input type="text" name="code" value="<?= $code ?>" hidden>
                                        <div class="input-group">
                                            <input type="password" name="pass" class="form-control eye"
                                                placeholder="PASSWORD" id="password1">
                                            <div class="input-group-text-trackless">
                                                <span>
                                                    <i class="fa fa-eye-slash" id="togglePassword1"
                                                        style="cursor: pointer"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="pswd_info">
                                            Password must meet the following requirements:
                                            <ul>
                                                <li id="letter" class="invalid">At least <strong>one letter</strong>
                                                </li>
                                                <li id="capital" class="invalid">At least <strong>one capital
                                                        letter</strong></li>
                                                <li id="number" class="invalid">At least <strong>one number</strong>
                                                </li>
                                                <li id="length" class="invalid">Be at least <strong>9
                                                        characters</strong>
                                                </li>
                                                <li id="special" class="invalid">Contains at least <strong>2 special
                                                        character</strong> ^!@#$%^&*\-_=+ </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-1 col-sm-1 col-md-1 d-flex align-items-center">
                                        <img src="" width="0" height="0" id="validpass" style="margin-left:15px">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="password" name="confirmpass" class="form-control eye"
                                                placeholder="CONFIRM PASSWORD" id="password2">
                                            <div class="input-group-text-trackless">
                                                <span>
                                                    <i class="fa fa-eye-slash" id="togglePassword2"
                                                        style="cursor: pointer"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-1 col-sm-1 col-md-1 d-flex align-items-center">
                                        <img src="" width="0" height="0" id="confpass" style="margin-left:15px">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mt-0">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-trackless mt-5">Confirm</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>