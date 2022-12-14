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
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="col-12 col-sm-6 col-md-6">
                            <?php if (isset($_SESSION["failed"])) { ?>
                            <div class="alert alert-warning" role="alert">
                                <?= @$_SESSION["failed"] ?>
                            </div>
                            <?php } ?>
        
                            <?php if (isset($_SESSION["success"])) { ?>
                            <div class="alert alert-info" role="alert">
                                <?= @$_SESSION["success"] ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <form method="POST" action="<?= base_url('email/resetpass'); ?>" autocomplete="off"
                        autocapitalize="none">
                        <div class="d-flex justify-content-center align-items-center">

                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="row">
                                    <div class="col-7">
                                        <input type="text" name="anonmail" class="form-control" id=""
                                            placeholder="EMAIL">
                                    </div>
                                    <div class="col-5 text-mail">
                                        @tracklessmail.com
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <input type="text" name="email" class="form-control" id=""
                                            placeholder="CONFIRM EMAIL TO RECOVERY PASSWORD">
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