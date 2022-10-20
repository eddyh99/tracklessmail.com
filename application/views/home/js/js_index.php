<script>
const togglePassword1 = document.querySelector("#togglePassword1");
const togglePassword2 = document.querySelector("#togglePassword2");
const password1 = document.querySelector("#password1");
const password2 = document.querySelector("#password2");

togglePassword1.addEventListener("click", function() {

    // toggle the type attribute
    const type = password1.getAttribute("type") === "password" ? "text" : "password";
    password1.setAttribute("type", type);
    // toggle the eye icon
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

togglePassword2.addEventListener("click", function() {

    // toggle the type attribute
    const type = password2.getAttribute("type") === "password" ? "text" : "password";
    password2.setAttribute("type", type);
    // toggle the eye icon
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

// cpanel
$(document).ready(function() {
    $('#password1').keyup(function() {
        var pswd = $(this).val();
        var c1 = 0;
        var c2 = 0;
        var c3 = 0;
        var c4 = 0;
        var c5 = 0;

        //validate the length
        if (pswd.length < 9) {
            $('#length').removeClass('valid').addClass('invalid');
            c1 = 0;
        } else {
            $('#length').removeClass('invalid').addClass('valid');
            c1 = 1;
        }

        //validate letter
        if (pswd.match(/[a-z]/)) {
            $('#letter').removeClass('invalid').addClass('valid');
            c2 = 1;
        } else {
            $('#letter').removeClass('valid').addClass('invalid');
            c2 = 0;
        }

        //validate capital letter
        if (pswd.match(/[A-Z]/)) {
            $('#capital').removeClass('invalid').addClass('valid');
            c3 = 1;
        } else {
            $('#capital').removeClass('valid').addClass('invalid');
            c3 = 0;
        }

        //validate number
        if (pswd.match(/\d/)) {
            $('#number').removeClass('invalid').addClass('valid');
            c4 = 1;
        } else {
            $('#number').removeClass('valid').addClass('invalid');
            c4 = 0;
        }

        if (pswd.match(/(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2}/)) {
            $('#special').removeClass('invalid').addClass('valid');
            c5 = 1;
        } else {
            $('#special').removeClass('valid').addClass('invalid');
            c5 = 0;
        }

        if (c1 && c2 && c3 && c4 && c5) {
            $('#validpass').show();
            $('#validpass').attr("width", 20);
            $('#validpass').attr("height", 20);
            $("#validpass").attr("src", "<?= base_url() ?>assets/images/valid.png");
        } else {
            $('#validpass').show();
            $('#validpass').attr("width", 20);
            $('#validpass').attr("height", 20);
            $("#validpass").attr("src", "<?= base_url() ?>assets/images/cross.png");
        }

    }).focus(function() {
        $('#pswd_info').show();
    }).blur(function() {
        $('#pswd_info').hide();
    });

    $('#password2').keyup(function() {
        var cpswd = $(this).val();
        var pswd = $("#password1").val();

        if ((cpswd == pswd) && cpswd.length > 0) {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/valid.png");
        } else {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/cross.png");
        }
    }).focus(function() {
        var cpswd = $(this).val();
        var pswd = $("#password1").val();
        if ((cpswd == pswd) && cpswd.length > 0) {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/valid.png");
        } else {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/cross.png");
        }
    }).blur(function() {
        var cpswd = $(this).val();
        var pswd = $("#password1").val();
        if ((cpswd == pswd) && cpswd.length > 0) {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/valid.png");
        } else {
            $('#confpass').show();
            $('#confpass').attr("width", 20);
            $('#confpass').attr("height", 20);
            $("#confpass").attr("src", "<?= base_url() ?>assets/images/cross.png");
        }
    });
});
// cpanel
</script>