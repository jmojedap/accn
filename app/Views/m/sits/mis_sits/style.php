<style>
    .sit-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .sit-item {
        display: flex;
        align-items: center;
        background: #fff;
        padding: 16px;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .sit-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-color: #e2e8f0;
    }

    .sit-thumb-container {
        flex-shrink: 0;
        margin-right: 20px;
    }

    .sit-thumb {
        width: 70px;
        height: 70px;
        border-radius: 50%; /* CIRCULAR */
        object-fit: cover;
        background-color: #f8fafc;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #e2e8f0;
        display: block;
    }

    .sit-info {
        flex-grow: 1;
        min-width: 0;
    }

    .sit-title {
        font-weight: 700;
        font-size: 1.15rem;
        margin-bottom: 2px;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sit-url {
        font-size: 0.85rem;
        color: #64748b;
        font-family: monospace;
        display: block;
    }

    .sit-actions {
        display: flex;
        gap: 10px;
        margin-left: 15px;
    }

    .btn-action-sit {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background-color: #f1f5f9;
        color: #475569;
        border: none;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-action-sit:hover {
        background-color: #e2e8f0;
        color: #0f172a;
    }

    .btn-action-sit.btn-delete:hover {
        background-color: #fee2e2;
        color: #dc2626;
    }

</style>