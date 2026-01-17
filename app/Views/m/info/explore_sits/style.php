<style>
/* Base & Typography - Minimalist */
@import url('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700&display=swap');

.sits-wrapper {
    position: relative;
    padding: 60px 20px;
    background-color: #ffffff;
    min-height: 100vh;
    font-family: 'Rubik', system-ui, -apple-system, sans-serif;
    color: #1a202c;
}

.relative-content {
    position: relative;
    z-index: 1;
}

.sits-wrapper .center_box_920 {
    max-width: 100%;
    width: 100%;
}

/* Header */
.header-section {
    margin-bottom: 50px;
    text-align: center;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1a202c;
    letter-spacing: -0.03em;
}

.title-underline {
    width: 60px;
    height: 4px;
    background: #F2403D;
    margin: 0 auto;
    border-radius: 2px;
}

/* Grid Layout */
.sits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

/* Card Design - Minimalist */
.sit-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.sit-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    border-color: #F2403D;
}

/* Image Area */
.card-image-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    background-color: #f7fafc;
}

.sit-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.sit-card:hover .sit-image {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sit-card:hover .card-overlay {
    opacity: 1;
}

/* Typography & Content */
.card-content {
    padding: 24px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.sit-category {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    color: #F2403D;
    margin-bottom: 8px;
    display: inline-block;
}

.sit-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    color: #1a202c;
    line-height: 1.3;
}

.sit-description {
    font-size: 0.95rem;
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 20px;
    flex-grow: 1;
}

/* Button */
.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: #F2403D;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transform: translateY(10px);
    transition: all 0.3s ease;
    border: none;
}

.sit-card:hover .btn-explore {
    transform: translateY(0);
}

.btn-explore:hover {
    background: #d62f2e;
    color: white;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .section-title {
        font-size: 2rem;
    }
    
    .sits-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
}
</style>