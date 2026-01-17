<style>
/* Base & Typography - Minimalist */
@import url('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700&display=swap');

.sits-wrapper {
    position: relative;
    width: 100%;
    min-height: 100vh;
    padding: 0;
    background-color: #ffffff;
    overflow-x: hidden;
    font-family: 'Rubik', system-ui, -apple-system, sans-serif;
    color: #1a202c;
}

.relative-content {
    position: relative;
    z-index: 1;
}

/* Typography */
.minimal-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1a202c;
    letter-spacing: -0.03em;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.minimal-title.large {
    font-size: 3.5rem;
    letter-spacing: -0.04em;
}

.minimal-title.large span {
    color: #F2403D; /* Updated Brand Color */
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Rubik', sans-serif;
    color: #1a202c;
}

.section-subtitle {
    font-size: 1.125rem;
    color: #4a5568;
    line-height: 1.6;
    max-width: 650px;
    margin: 0 auto 2.5rem auto;
    font-weight: 300;
}

/* Minimal Cards */
.minimal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2.5rem 2rem;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.minimal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    border-color: #F2403D;
}

/* Icons within cards */
.minimal-card i {
    font-size: 2.5rem;
    color: #F2403D;
    margin-bottom: 1rem;
    display: block;
}

/* Buttons */
.btn-firm-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 16px 36px;
    background: #F2403D;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-firm-primary:hover {
    background: #d62f2e;
    color: white;
    transform: translateY(-1px);
}

.btn-firm-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 32px;
    background: transparent;
    color: #F2403D;
    border: 2px solid #F2403D;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.btn-firm-secondary:hover {
    background: #fff5f5;
    color: #d62f2e;
    border-color: #d62f2e;
}

/* Utils */
.text-brand { color: #F2403D !important; }
.bg-subtle { background-color: #f8f9fa; }
.py-section { padding: 6rem 0; }
.separator-line {
    height: 4px; 
    width: 60px; 
    background: #F2403D; 
    margin: 1.5rem auto; 
    border-radius: 2px;
}
.icon-circle {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    color: #F2403D;
}

/* Animations */
.fade-in-up {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.fade-in-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .minimal-title.large { font-size: 2.5rem; }
    .minimal-title { font-size: 2rem; }
}

/* Overrides for specific sections if needed */
.hero-section {
  background: white; /* Clean background */
  padding-top: 5rem;
  padding-bottom: 5rem;
}
</style>