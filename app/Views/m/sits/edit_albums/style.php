<style>
    .album-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .album-item {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        border: 1px solid transparent;
    }

    .album-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.08);
        border-color: #0d6efd20;
    }

    .album-thumb-container {
        flex-shrink: 0;
        margin-right: 16px;
    }

    .album-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        background-color: #f8f9fa;
        display: block;
    }

    .album-info {
        flex-grow: 1;
        min-width: 0;
    }

    .album-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 4px;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .album-excerpt {
        font-size: 0.9rem;
        color: #6c757d;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .album-actions {
        display: flex;
        gap: 8px;
        margin-left: 12px;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f8f9fa;
        color: #6c757d;
        border: none;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: #e9ecef;
        color: #212529;
    }

    .btn-action.btn-delete:hover {
        background: #fee2e2;
        color: #dc2626;
    }
</style>
