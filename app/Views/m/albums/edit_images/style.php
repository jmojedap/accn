<style>
    #image_gallery .card {
        border: none;
        border-radius: 12px;
        /* overflow removed to allow dropdown to show outside the card */
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        cursor: grab;
    }

    #image_gallery .card:active {
        cursor: grabbing;
    }

    #image_gallery .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.1);
    }

    .image-container {
        position: relative;
    }

    .sqr-180 {
        width: 180px;
        height: 180px;
        object-fit: cover;
        display: block;
        border-radius: 12px; /* Apply radius directly to image */
    }

    .btn-circle {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(0,0,0,0.1);
        color: #444;
        transition: all 0.2s;
    }

    .btn-circle:hover {
        background: #fff;
        color: #000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .dropdown-options {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 100 !important; /* Higher z-index to stay on top */
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    #image_gallery .card:hover .dropdown-options,
    .dropdown-options.show {
        opacity: 1;
        visibility: visible;
    }

    .main-image-indicator {
        position: absolute;
        top: 8px;
        left: 8px;
        background: #0d6efd;
        color: white;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        pointer-events: none;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-radius: 10px;
        padding: 8px;
    }

    .dropdown-item {
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dropdown-item i {
        width: 16px;
        text-align: center;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    .dropdown-item.text-danger:hover {
        background-color: #fff5f5;
        color: #dc3545 !important;
    }

    .sortable-ghost {
        opacity: 0.4;
        filter: grayscale(1);
    }
</style>