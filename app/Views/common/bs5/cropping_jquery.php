<?= view('assets/cropper') ?>

<script>
// Variables
//-----------------------------------------------------------------------------
var imageId = '<?= $imageId ?>';
var backDestination = '<?= $backDestination ?>';

// Document Ready
//-----------------------------------------------------------------------------

    $(document).ready(function ()
    {
        $("#crop_form").submit(function () {
            $.ajax({
                type: "POST",
                url: URL_API + 'files/crop/' + imageId,
                data: $("#crop_form").serialize(),
                success: function (response) {
                    after_submit(response);
                },
                error: function () {
                    toastr["error"]('Ocurrió un error al recortar');
                }
            });

            return false;
        });
    });

//Funciones
//---------------------------------------------------------------------------------------------------

//Después de recibir el response del envío del crop_form
function after_submit(response)
{
    if ( response.status == 1 )
    {
        window.location = URL_APP + back_destination
    } else {
        toastr['error'](response.message)
    }
}
</script>

<div class="row">
    <div class="col-md-9">
        <div class="img-container">
            <img id="image" src="<?= $urlImage ?>" alt="Imagen para recortar">
        </div>
    </div>
    <div class="col-md-3">
        
        <div class="docs-preview clearfix">
            <div class="img-preview preview-lg"></div>
            <div class="img-preview preview-md"></div>
            <div class="img-preview preview-sm"></div>
            <div class="img-preview preview-xs"></div>
        </div>
        
        <form id="crop_form" method="post">
            <div class="docs-data">
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
            

            <a class="btn btn-secondary w120p" href="<?= $backDestination ?>">
                <i class="fa fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-success w120p">Recortar</button>
        
            <br/>
        
            <div class="docs-toggles">
                <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-default">
                        <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Proporción: 16 / 9">
                            16:9
                        </span>
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Proporción: 4 / 3">
                            4:3
                        </span>
                    </label>
                    <label class="btn btn-default active">
                        <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Proporción: 1 / 1">
                            1:1
                        </span>
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Proporción: 2 / 3">
                            2:3
                        </span>
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Proporción: Manual">
                            Manual
                        </span>
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>