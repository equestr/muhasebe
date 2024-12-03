<header class="header">
    <a href="https://ersinemlak.com.tr" class="logo">
        <img src="../logo1.png" alt="ERS GROUP A.Ş." class="logo-img"> Ofis Yönetimi
    </a>
    <div class="header-right">
        <a href="users.php?edit=<?=$admin->id;?>" class="user-link">
            <i class="fas fa-user-circle"></i> <?=$admin->fullname;?>
        </a>
        <a href="../personel/" class="personnel-link">
            <i class="fas fa-users"></i> Personel Sistemi
        </a>
        <a href="logout.php" class="logout-link">
            <i class="fas fa-power-off"></i> Çıkış Yap
        </a>
        <button class="mobile-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<?php $count = $pia->get_var("SELECT COUNT(*) as count FROM reminders WHERE status=0"); ?>

<div class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="main.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="financial_dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Finansal Panel</span>
                </a>
            </li>
            <li>
                <a href="chatbot.php">
                    <i class="fas fa-microchip"></i>
                    <span>Yapay Zeka</span>
                </a>
            </li>
            <li>
                <a href="income.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Gelirler</span>
                </a>
            </li>
            <li>
                <a href="expense.php">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Giderler</span>
                </a>
            </li>
            <li>
                <a href="customers.php">
                    <i class="fas fa-users"></i>
                    <span>Cariler</span>
                </a>
            </li>
            <li>
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Kategoriler</span>
                </a>
            </li>
            <li>
    <a href="safes.php">
        <i class="fas fa-money-check-alt"></i>
        <span>Kasa Yönetimi</span>
    </a>
</li>
            <li>
                <a href="pending_payments.php">
                    <i class="fas fa-clock"></i>
                    <span>Bekleyen Ödemeler</span>
                </a>
            </li>
            <li>
                <a href="reminders.php">
                    <i class="fas fa-bell"></i>
                    <span>Hatırlatmalar</span>
                    <?php if($count>0){ ?>
                        <span class="notification-badge"><?=$count;?></span>
                    <?php } ?>
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Raporlar</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-user-cog"></i>
                    <span>Kullanıcılar</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<div class="loader">
    <div class="spinner">
        <i class="fas fa-spinner fa-spin"></i>
    </div>
</div>

<div class="loader">
    <div class="spinner">
        <i class="fas fa-spinner-third"></i>
    </div>
</div>

<style>
.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo-img {
    height: 40px; /* Logo yüksekliğini ayarlayabilirsiniz */
    margin-right: 10px;
    object-fit: contain;
}
.personnel-link {
    display: inline-flex;
    align-items: center;
    padding: 0 15px;
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.personnel-link i {
    margin-right: 5px;
}

.personnel-link:hover {
    color: #007bff;
}
:root {
    --primary-color: #1a1a1a;
    --secondary-color: #2d2d2d;
    --accent-color: #3498db;
    --text-color: #ffffff;
    --hover-color: #3a3a3a;
    --sidebar-width: 260px;
    --header-height: 65px;
}

.header {
    background: var(--primary-color);
    height: var(--header-height);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.header a {
    color: var(--text-color);
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header a:hover {
    background: var(--hover-color);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.logo {
    font-size: 1.3rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.logo i {
    color: var(--accent-color);
}

.sidebar {
    background: var(--primary-color);
    width: var(--sidebar-width);
    height: calc(100vh - var(--header-height));
    position: fixed;
    top: var(--header-height);
    left: 0;
    overflow-y: auto;
    transition: all 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
}

.sidebar-nav ul {
    list-style: none;
    padding: 15px 0;
    margin: 0;
}

.sidebar-nav li a {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: var(--text-color);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-nav li a:hover {
    background: var(--hover-color);
    border-left: 3px solid var(--accent-color);
}

.sidebar-nav i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
    font-size: 1.1rem;
    color: var(--accent-color);
}

.notification-badge {
    background: #e74c3c;
    color: white;
    border-radius: 12px;
    padding: 3px 8px;
    font-size: 12px;
    margin-left: auto;
    font-weight: 600;
}

.loader {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.spinner {
    color: var(--accent-color);
    font-size: 2.5rem;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.mobile-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--text-color);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.mobile-toggle:hover {
    background: var(--hover-color);
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .mobile-toggle {
        display: block;
    }
    
    .header-right a span {
        display: none;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 0 15px;
    }
    
    .logo {
        font-size: 1.1rem;
    }
}

/* Scrollbar Tasarımı */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: var(--primary-color);
}

.sidebar::-webkit-scrollbar-thumb {
    background: var(--hover-color);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--accent-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    mobileToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    // Aktif menü öğesini vurgulama
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-nav a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath.split('/').pop()) {
            item.style.background = 'var(--hover-color)';
            item.style.borderLeft = '3px solid var(--accent-color)';
        }
    });
});
</script>