<?php
    $random = rand(100,999);
?>

<script>
var newPost = {
    type_id: 1,
    title: 'Nuevo post',    
    content: 'Nunc rhoncus varius quam, at euismod tortor venenatis eget. Vestibulum nec ipsum vitae ligula interdum posuere. Morbi a rhoncus enim, in sodales neque.'
}

// VueApp
//-----------------------------------------------------------------------------
var addPost = createApp({
    data() {
        return {
            entityInfo: <?= json_encode($entityInfo) ?>,
            urlMod: URL_MOD,
            post: newPost,
            arrTypes: <?= json_encode($arrTypes) ?>,
            rowId: 0,
            idCode: 0,
            loading: false,
        }
    },
    methods: {
        handleSubmit: function() {
            var formData = new FormData(document.getElementById('addForm'))
            axios.post(URL_API + 'posts/create/', formData)
                .then(response => {
                    if (response.data.savedId > 0) {
                        this.rowId = response.data.savedId
                        this.idCode = response.data.idcode
                        createdModal.show()
                    } else {
                        toastr['error']('No se guardó');
                    }
                    this.loading = false
                })
                .catch(function(error) {
                    console.log(error)
                })
        },
        goToCreated: function(){
            window.location = URL_MOD + `posts/info/` + this.idCode
        },
    }
}).mount('#addPost')

var createdModal = new bootstrap.Modal(document.getElementById('createdModal'))
</script>