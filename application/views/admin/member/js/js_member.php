<script>
var i = 1;
$(function() {
    $('#member').DataTable({
        "scrollX": true,
        "responsive": true,
        "ajax": {
            "url": "<?= base_url() ?>cpanel/member/get_all",
            "type": "POST",
            "data": function(d) {
                d.csrf_freedy = $("#token").val(),
                    d.bank_id = $("#bank").val()
            },
            "dataSrc": function(data) {
                $("#token").val(data["token"]);
                console.log(data["member"]);
                return data["member"];
            },
        },
        order: [
            [0, 'asc']
        ],
        "pageLength": 100,
        "columns": [{
                "mRender": function(data, type, full, meta) {
                    return i++;
                }
            },
            {
                "data": "anonmail"
            },
            {
                "data": "email"
            },
            {
                "data": "timecreate"
            },
        ],
    });
})
</script>