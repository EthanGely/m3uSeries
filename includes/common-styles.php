<style>
    body {
        background: linear-gradient(135deg, #141414 0%, #1a1a1a 100%);
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }
    
    .header {
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        padding: 1.5rem 0;
        margin-bottom: 2rem;
        border-bottom: 2px solid #e50914;
    }
    
    .back-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .back-btn:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }
    
    .btn-favorite {
        background: linear-gradient(45deg, #e50914, #f40612);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-favorite:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        color: white;
    }
    
    .btn-favorite.is-favorite {
        background: linear-gradient(45deg, #ffd700, #ffed4e);
        color: #333;
    }
</style>