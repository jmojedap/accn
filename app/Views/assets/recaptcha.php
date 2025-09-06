<script src="https://www.google.com/recaptcha/api.js?render=<?= K_RCSK ?>"></script>
<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('<?= K_RCSK ?>', {action: 'homepage'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
</script>