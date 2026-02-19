<?= view('assets/cropper') ?>

<div id="croppingApp">
    <div class="row">
        <div class="col-md-8">
            <div class="img-container">
                <img id="image" src="<?= $urlImage ?>" alt="Imagen para recortar">
            </div>
        </div>
        <div class="col-md-4">
            
            <div class="docs-preview clearfix">
                <div class="img-preview preview-lg"></div>
                <div class="img-preview preview-md d-none"></div>
                <div class="img-preview preview-sm d-none"></div>
                <div class="img-preview preview-xs d-none"></div>
            </div>
            
            <form id="crop-form" method="post" @submit.prevent="handleSubmit">
                <div class="docs-data d-none">
                    <div class="input-group input-group-sm">
                        <label class="input-group-addon" for="dataX">X</label>
                        <input type="text" class="form-control" id="dataX" placeholder="x" name="x_axis">
                    </div>
                    <div class="input-group input-group-sm">
                        <label class="input-group-addon" for="dataY">Y</label>
                        <input type="text" class="form-control" id="dataY" placeholder="y" name="y_axis">
                    </div>
                    <div class="input-group input-group-sm">
                        <label class="input-group-addon" for="dataWidth">Width</label>
                        <input type="text" class="form-control" id="dataWidth" placeholder="width" name="width">
                    </div>
                    <div class="input-group input-group-sm">
                        <label class="input-group-addon" for="dataHeight">Height</label>
                        <input type="text" class="form-control" id="dataHeight" placeholder="height" name="height">
                    </div>
                </div>
                

                <a class="btn btn-secondary w120p me-1" href="<?= $backDestination ?>">
                    <i class="fa fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success w120p">Recortar</button>
            
                <br/>
            
                <div class="docs-toggles mt-3">
                    <div class="btn-group btn-group-justified">
                        <template v-for="aspectRatioOption in aspectRatioOptions">
                            <label class="btn" :class="aspectRatio == aspectRatioOption.value ? 'btn-primary' : 'btn-light'" v-show="activeRatios.includes(aspectRatioOption.label)">
                                <input type="radio" class="sr-only" :id="'aspectRatio' + aspectRatioOption.value" name="aspectRatio" :value="aspectRatioOption.value" v-model="aspectRatio">
                                <span class="docs-tooltip" data-toggle="tooltip" :title="aspectRatioOption.title">
                                    {{ aspectRatioOption.label }}
                                </span>
                            </label>
                        </template>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var croppingApp = createApp({
    data(){
        return{
            loading: false,
            fields: {},
            imageId: '<?= $imageId ?>',
            activeRatios: <?= json_encode($activeRatios) ?>,
            aspectRatioOptions: [
                {value: '1.7777777777777777', label: '16:9', title: 'Proporción: 16 / 9'},
                {value: '0.5625', label: '9:16', title: 'Proporción: 9 / 16'},
                {value: '1.3333333333333333', label: '4:3', title: 'Proporción: 4 / 3'},
                {value: '1', label: '1:1', title: 'Proporción: 1 / 1'},
                {value: '0.6666666666666666', label: '2:3', title: 'Proporción: 2 / 3'},
                {value: 'NaN', label: 'Manual', title: 'Proporción: Manual'},
            ],
            aspectRatio: '1',
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('crop-form'))
            axios.post(URL_API + 'files/crop/' + this.imageId, formValues)
            .then(response => {
                if ( response.data.status == 1 ) {
                    toastr['success']('Imagen recortada')
                    setTimeout(function(){
                        window.location = '<?= $backDestination ?>'
                    }, 1000)
                } else {
                    toastr['error'](response.data.message)
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    },
}).mount('#croppingApp')
</script>