<div class="center_box_750">
    <div class="row">
        <?php foreach ($albums as $album) : ?>
            <div class="col-md-6 mb-4">
                <a href="<?= base_url("m/albums/images/{$album->id}") ?>" class="card album-card text-decoration-none h-100 border-0 shadow-sm overflow-hidden">
                    <div class="album-img-wrapper position-relative">
                        <img 
                            src="<?= $album->url_image ?>" 
                            class="card-img-top album-thumbnail" 
                            alt="<?= $album->title ?>"
                            onerror="this.src='<?= URL_IMG ?>app/album-default.png'"
                        >
                        <div class="album-overlay d-flex align-items-center justify-content-center">
                            <i class="fa fa-camera fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark mb-1"><?= $album->title ?></h5>
                        <p class="card-text text-muted small mb-0"><?= $album->excerpt ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($albums) == 0) : ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state">
                    <i class="fa fa-images fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-secondary lead">No hay álbumes de fotos disponibles.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .album-card { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 12px; }
    .album-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    
    .album-img-wrapper { height: 180px; overflow: hidden; }
    .album-thumbnail { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
    .album-card:hover .album-thumbnail { transform: scale(1.1); }
    
    .album-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.3s ease; }
    .album-card:hover .album-overlay { opacity: 1; }
    
    .empty-state i { display: block; }
</style>
