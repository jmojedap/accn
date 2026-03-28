<div class="center_box_750">
    <div class="d-flex align-items-center mb-4">
        <a href="javascript:history.back()" class="btn btn-light rounded-circle me-3" title="Volver">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold"><?= $row->title ?></h2>
    </div>

    <div class="row g-3">
        <?php foreach ($images->getResult() as $image) : ?>
            <div class="col-6 col-md-4">
                <div class="image-item position-relative overflow-hidden rounded-3 shadow-sm bg-light" 
                     data-bs-toggle="modal" 
                     data-bs-target="#imageModal" 
                     onclick="setModalImage('<?= $image->url ?>', '<?= htmlspecialchars($image->title) ?>')">
                    
                    <img src="<?= $image->url_thumbnail ?>" 
                         class="img-fluid w-100 image-thumb" 
                         alt="<?= $image->title ?>"
                         loading="lazy"
                         onerror="this.src='<?= URL_IMG ?>app/image-error.png'">
                    
                    <div class="image-overlay d-flex align-items-center justify-content-center">
                        <i class="fa fa-search-plus text-white fa-2x"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if ($images->getNumRows() == 0) : ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state opacity-25">
                    <i class="fa fa-image fa-4x text-muted mb-3"></i>
                    <p class="lead">Este álbum aún no tiene fotos disponibles.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img src="" id="modalLargeImage" class="img-fluid rounded shadow-lg" alt="Vista ampliada">
                <div id="modalImageTitle" class="text-white mt-3 fw-light"></div>
            </div>
        </div>
    </div>
</div>

<script>
function setModalImage(url, title) {
    document.getElementById('modalLargeImage').src = url;
    document.getElementById('modalImageTitle').innerText = title;
}
</script>

<style>
    .image-item { cursor: pointer; aspect-ratio: 1/1; }
    .image-thumb { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
    .image-item:hover .image-thumb { transform: scale(1.08); }
    
    .image-overlay { 
        position: absolute; top: 0; left: 0; 
        width: 100%; height: 100%; 
        background: rgba(0,0,0,0.3); 
        opacity: 0; transition: opacity 0.3s ease; 
    }
    .image-item:hover .image-overlay { opacity: 1; }
    
    #modalLargeImage { max-height: 85vh; width: auto; }
</style>
