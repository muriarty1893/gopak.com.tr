<?php
session_start();

// Admin giriş kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Admin bilgilerini al
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gopak Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-section {
            min-height: 100vh;
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .admin-title {
            color: #333;
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .admin-subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .admin-nav {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            background: white;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .admin-nav button {
            background: none;
            border: none;
            padding: 12px 24px;
            margin: 0 4px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .admin-nav button.active {
            background: #FF6000;
            color: white;
        }
        
        .admin-nav button:hover:not(.active) {
            background: #f0f0f0;
            color: #333;
        }
        
        .admin-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            min-height: 500px;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-title {
            font-size: 1.8em;
            color: #333;
            font-weight: 600;
        }
        
        .add-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .add-button:hover {
            background: #218838;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .btn-edit:hover { background: #e0a800; }
        .btn-delete:hover { background: #c82333; }
        .btn-cancel:hover { background: #5a6268; }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-production { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .modal-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
        }
        
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary:hover { background: #5a6268; }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #FF6000;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: 700;
            color: #FF6000;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1em;
        }
        
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-size: 0.9em;
        }
        
        .user-info .username {
            font-weight: 600;
            color: #FF6000;
        }
        
        .user-info .role {
            color: #666;
            font-size: 0.8em;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 8px;
            }
            
            .admin-nav button {
                margin: 0;
            }
            
            .data-table {
                font-size: 0.9em;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            
            .user-info {
                position: static;
                margin-bottom: 20px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-section">
        <div class="admin-container">
            <!-- Kullanıcı Bilgileri -->
            <div class="user-info">
                <span class="username"><?php echo htmlspecialchars($admin_username); ?></span>
                <span class="role">(<?php echo htmlspecialchars($admin_role); ?>)</span>
                <button class="logout-btn" onclick="logout()">Çıkış</button>
            </div>
            
            <a href="index.html" class="back-link">← Ana Sayfaya Dön</a>
            
            <div class="admin-header">
                <h1 class="admin-title">🔐 Gopak Admin Panel</h1>
                <p class="admin-subtitle">Siparişleri, müşterileri ve ürünleri yönetin</p>
            </div>
            
            <div class="admin-nav">
                <button class="nav-btn active" data-section="dashboard">📊 Dashboard</button>
                <button class="nav-btn" data-section="orders">📋 Siparişler</button>
                <button class="nav-btn" data-section="customers">👥 Müşteriler</button>
                <button class="nav-btn" data-section="products">🛍️ Ürünler</button>
            </div>
            
            <div class="admin-content">
                <!-- Dashboard Section -->
                <div id="dashboard" class="content-section active">
                    <div class="section-header">
                        <h2 class="section-title">📊 Dashboard</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number" id="totalOrders">-</div>
                            <div class="stat-label">Toplam Sipariş</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="totalCustomers">-</div>
                            <div class="stat-label">Toplam Müşteri</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="totalProducts">-</div>
                            <div class="stat-label">Toplam Ürün</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="pendingOrders">-</div>
                            <div class="stat-label">Bekleyen Sipariş</div>
                        </div>
                    </div>
                    <div id="dashboardContent">
                        <div class="loading">Dashboard yükleniyor...</div>
                    </div>
                </div>
                
                <!-- Orders Section -->
                <div id="orders" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">📋 Siparişler</h2>
                    </div>
                    <div id="ordersContent">
                        <div class="loading">Siparişler yükleniyor...</div>
                    </div>
                </div>
                
                <!-- Customers Section -->
                <div id="customers" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">👥 Müşteriler</h2>
                    </div>
                    <div id="customersContent">
                        <div class="loading">Müşteriler yükleniyor...</div>
                    </div>
                </div>
                
                <!-- Products Section -->
                <div id="products" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">🛍️ Ürünler</h2>
                        <button class="add-button" onclick="showAddProductModal()">+ Yeni Ürün Ekle</button>
                    </div>
                    <div id="productsContent">
                        <div class="loading">Ürünler yükleniyor...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Yeni Ürün Ekle</h3>
                <span class="close" onclick="closeModal('addProductModal')">&times;</span>
            </div>
            <form id="addProductForm">
                <div class="form-group">
                    <label for="productName">Ürün Adı *</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productDescription">Açıklama</label>
                    <textarea id="productDescription" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="productPrice">Fiyat (₺) *</label>
                    <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="productStock">Stok Miktarı *</label>
                    <input type="number" id="productStock" name="stock_quantity" min="0" required>
                </div>
                <div class="form-group">
                    <label for="productCategory">Kategori</label>
                    <select id="productCategory" name="category">
                        <option value="Standart">Standart</option>
                        <option value="Özel">Özel</option>
                        <option value="Premium">Premium</option>
                        <option value="Çanta">Çanta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productBagType">Çanta Tipi</label>
                    <select id="productBagType" name="bag_type">
                        <option value="">Çanta değil</option>
                        <option value="3D Çanta (Yan Körüklü)">3D Çanta (Yan Körüklü)</option>
                        <option value="Düz Çanta (Yan Körüksüz)">Düz Çanta (Yan Körüksüz)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productBagDimensions">Çanta Boyutları</label>
                    <input type="text" id="productBagDimensions" name="bag_dimensions" placeholder="örn: 30 × 25 × 10">
                </div>
                <div class="form-group">
                    <label for="productMinOrderQuantity">Minimum Sipariş Miktarı</label>
                    <input type="number" id="productMinOrderQuantity" name="min_order_quantity" min="0" placeholder="örn: 2500">
                </div>
                <div class="form-group">
                    <label for="productBagDescription">Çanta Açıklaması</label>
                    <textarea id="productBagDescription" name="bag_description" placeholder="örn: Pastane ve tatlıcılar için uygun"></textarea>
                </div>
                <div class="form-group">
                    <label for="productImage">Resim URL</label>
                    <input type="url" id="productImage" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="productCustom" name="is_custom" value="1">
                        Özel ürün mü?
                    </label>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('addProductModal')">İptal</button>
                    <button type="submit" class="btn-primary">Ürün Ekle</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ürün Düzenle</h3>
                <span class="close" onclick="closeModal('editProductModal')">&times;</span>
            </div>
            <form id="editProductForm">
                <input type="hidden" id="editProductId" name="id">
                <div class="form-group">
                    <label for="editProductName">Ürün Adı *</label>
                    <input type="text" id="editProductName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editProductDescription">Açıklama</label>
                    <textarea id="editProductDescription" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="editProductPrice">Fiyat (₺) *</label>
                    <input type="number" id="editProductPrice" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="editProductStock">Stok Miktarı *</label>
                    <input type="number" id="editProductStock" name="stock_quantity" min="0" required>
                </div>
                <div class="form-group">
                    <label for="editProductCategory">Kategori</label>
                    <select id="editProductCategory" name="category">
                        <option value="Standart">Standart</option>
                        <option value="Özel">Özel</option>
                        <option value="Premium">Premium</option>
                        <option value="Çanta">Çanta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editProductBagType">Çanta Tipi</label>
                    <select id="editProductBagType" name="bag_type">
                        <option value="">Çanta değil</option>
                        <option value="3D Çanta (Yan Körüklü)">3D Çanta (Yan Körüklü)</option>
                        <option value="Düz Çanta (Yan Körüksüz)">Düz Çanta (Yan Körüksüz)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editProductBagDimensions">Çanta Boyutları</label>
                    <input type="text" id="editProductBagDimensions" name="bag_dimensions" placeholder="örn: 30 × 25 × 10">
                </div>
                <div class="form-group">
                    <label for="editProductMinOrderQuantity">Minimum Sipariş Miktarı</label>
                    <input type="number" id="editProductMinOrderQuantity" name="min_order_quantity" min="0" placeholder="örn: 2500">
                </div>
                <div class="form-group">
                    <label for="editProductBagDescription">Çanta Açıklaması</label>
                    <textarea id="editProductBagDescription" name="bag_description" placeholder="örn: Pastane ve tatlıcılar için uygun"></textarea>
                </div>
                <div class="form-group">
                    <label for="editProductImage">Resim URL</label>
                    <input type="url" id="editProductImage" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="editProductCustom" name="is_custom" value="1">
                        Özel ürün mü?
                    </label>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('editProductModal')">İptal</button>
                    <button type="submit" class="btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Sipariş Detayları</h3>
                <span class="close" onclick="closeModal('orderDetailsModal')">&times;</span>
            </div>
            <div id="orderDetailsContent">
                <div class="loading">Sipariş detayları yükleniyor...</div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentSection = 'dashboard';
        let orders = [];
        let customers = [];
        let products = [];
        
        // Logout function
        async function logout() {
            try {
                const response = await fetch('api/admin_logout.php', {
                    method: 'POST'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = result.redirect;
                    }
                }
            } catch (error) {
                console.error('Çıkış hatası:', error);
                // Hata olsa bile login sayfasına yönlendir
                window.location.href = 'admin_login.php';
            }
        }
        
        // Navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const section = btn.dataset.section;
                showSection(section);
            });
        });
        
        function showSection(sectionName) {
            // Update navigation
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');
            
            // Update content
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionName).classList.add('active');
            
            currentSection = sectionName;
            
            // Load section data
            switch(sectionName) {
                case 'dashboard':
                    loadDashboard();
                    break;
                case 'orders':
                    loadOrders();
                    break;
                case 'customers':
                    loadCustomers();
                    break;
                case 'products':
                    loadProducts();
                    break;
            }
        }
        
        // Dashboard
        async function loadDashboard() {
            try {
                const [ordersRes, customersRes, productsRes] = await Promise.all([
                    fetch('api/admin_orders.php'),
                    fetch('api/admin_customers.php'),
                    fetch('api/admin_products.php')
                ]);
                
                if (ordersRes.ok && customersRes.ok && productsRes.ok) {
                    const ordersData = await ordersRes.json();
                    const customersData = await customersRes.json();
                    const productsData = await productsRes.json();
                    
                    orders = ordersData.orders || [];
                    customers = customersData.customers || [];
                    products = productsData.products || [];
                    
                    updateDashboardStats();
                    displayRecentOrders();
                } else {
                    throw new Error('API yanıtları başarısız');
                }
            } catch (error) {
                console.error('Dashboard yükleme hatası:', error);
                document.getElementById('dashboardContent').innerHTML = `
                    <div class="error-message">
                        Dashboard yüklenirken hata oluştu: ${error.message}
                    </div>
                `;
            }
        }
        
        function updateDashboardStats() {
            document.getElementById('totalOrders').textContent = orders.length;
            document.getElementById('totalCustomers').textContent = customers.length;
            document.getElementById('totalProducts').textContent = products.length;
            document.getElementById('pendingOrders').textContent = orders.filter(o => o.status === 'pending').length;
        }
        
        function displayRecentOrders() {
            const recentOrders = orders.slice(0, 5);
            const ordersHtml = recentOrders.length > 0 ? 
                recentOrders.map(order => `
                    <div class="order-card" style="background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border: 1px solid #e9ecef;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>#${order.order_number}</strong> - ${order.customer_name}
                            </div>
                            <span class="status-badge status-${order.status}">${getStatusText(order.status)}</span>
                        </div>
                        <div style="color: #666; font-size: 0.9em; margin-top: 5px;">
                            ${new Date(order.created_at).toLocaleDateString('tr-TR')} - ${order.total}₺
                        </div>
                    </div>
                `).join('') :
                '<p style="text-align: center; color: #666;">Henüz sipariş bulunmuyor.</p>';
            
            document.getElementById('dashboardContent').innerHTML = `
                <h3 style="margin-bottom: 20px; color: #333;">Son Siparişler</h3>
                ${ordersHtml}
            `;
        }
        
        // Orders
        async function loadOrders() {
            try {
                const response = await fetch('api/admin_orders.php');
                if (response.ok) {
                    const data = await response.json();
                    orders = data.orders || [];
                    displayOrders();
                } else {
                    throw new Error('Siparişler yüklenemedi');
                }
            } catch (error) {
                console.error('Sipariş yükleme hatası:', error);
                document.getElementById('ordersContent').innerHTML = `
                    <div class="error-message">
                        Siparişler yüklenirken hata oluştu: ${error.message}
                    </div>
                `;
            }
        }
        
        function displayOrders() {
            if (orders.length === 0) {
                document.getElementById('ordersContent').innerHTML = `
                    <div class="no-orders">
                        <p>Henüz sipariş bulunmuyor.</p>
                    </div>
                `;
                return;
            }
            
            const ordersHtml = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Müşteri</th>
                            <th>Ürün</th>
                            <th>Miktar</th>
                            <th>Toplam</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td>#${order.order_number}</td>
                                <td>${order.customer_name}</td>
                                <td>${order.product_type} - ${order.product_color} (${order.product_size}cm)</td>
                                <td>${order.quantity}</td>
                                <td>${order.total}₺</td>
                                <td>
                                    <span class="status-badge status-${order.status}">
                                        ${getStatusText(order.status)}
                                    </span>
                                </td>
                                <td>${new Date(order.created_at).toLocaleDateString('tr-TR')}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="viewOrderDetails(${order.id})">Görüntüle</button>
                                        <button class="btn-cancel" onclick="cancelOrder(${order.id})">İptal Et</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('ordersContent').innerHTML = ordersHtml;
        }
        
        // Customers
        async function loadCustomers() {
            try {
                const response = await fetch('api/admin_customers.php');
                if (response.ok) {
                    const data = await response.json();
                    customers = data.customers || [];
                    displayCustomers();
                } else {
                    throw new Error('Müşteriler yüklenemedi');
                }
            } catch (error) {
                console.error('Müşteri yükleme hatası:', error);
                document.getElementById('customersContent').innerHTML = `
                    <div class="error-message">
                        Müşteriler yüklenirken hata oluştu: ${error.message}
                    </div>
                `;
            }
        }
        
        function displayCustomers() {
            if (customers.length === 0) {
                document.getElementById('customersContent').innerHTML = `
                    <div class="no-orders">
                        <p>Henüz müşteri bulunmuyor.</p>
                    </div>
                `;
                return;
            }
            
            const customersHtml = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Telefon</th>
                            <th>Adres</th>
                            <th>Kayıt Tarihi</th>
                            <th>Toplam Sipariş</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${customers.map(customer => {
                            const customerOrders = orders.filter(o => o.customer_id === customer.id);
                            const totalSpent = customerOrders.reduce((sum, o) => sum + parseFloat(o.total), 0);
                            return `
                                <tr>
                                    <td>${customer.id}</td>
                                    <td>${customer.first_name} ${customer.last_name}</td>
                                    <td>${customer.email}</td>
                                    <td>${customer.phone}</td>
                                    <td>${customer.address}</td>
                                    <td>${new Date(customer.created_at).toLocaleDateString('tr-TR')}</td>
                                    <td>${customerOrders.length} (${totalSpent.toFixed(2)}₺)</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('customersContent').innerHTML = customersHtml;
        }
        
        // Products
        async function loadProducts() {
            try {
                const response = await fetch('api/admin_products.php');
                if (response.ok) {
                    const data = await response.json();
                    products = data.products || [];
                    displayProducts();
                } else {
                    throw new Error('Ürünler yüklenemedi');
                }
            } catch (error) {
                console.error('Ürün yükleme hatası:', error);
                document.getElementById('productsContent').innerHTML = `
                    <div class="error-message">
                        Ürünler yüklenirken hata oluştu: ${error.message}
                    </div>
                `;
            }
        }
        
        function displayProducts() {
            if (products.length === 0) {
                document.getElementById('productsContent').innerHTML = `
                    <div class="no-orders">
                        <p>Henüz ürün bulunmuyor.</p>
                    </div>
                `;
                return;
            }
            
            const productsHtml = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Kategori</th>
                            <th>Çanta Tipi</th>
                            <th>Boyutlar</th>
                            <th>Min. Sipariş</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Özel</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${products.map(product => `
                            <tr>
                                <td>${product.id}</td>
                                <td>${product.name}</td>
                                <td>${product.category}</td>
                                <td>${product.bag_type || '-'}</td>
                                <td>${product.bag_dimensions || '-'}</td>
                                <td>${product.min_order_quantity ? product.min_order_quantity.toLocaleString() : '-'}</td>
                                <td>${product.price}₺</td>
                                <td>${product.stock_quantity}</td>
                                <td>${product.is_custom ? 'Evet' : 'Hayır'}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="editProduct(${product.id})">Düzenle</button>
                                        <button class="btn-delete" onclick="deleteProduct(${product.id})">Sil</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('productsContent').innerHTML = productsHtml;
        }
        
        // Product Management
        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
            document.getElementById('addProductForm').reset();
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        document.getElementById('addProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const productData = Object.fromEntries(formData.entries());
            productData.is_custom = formData.has('is_custom');
            
            try {
                const response = await fetch('api/admin_products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(productData)
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        alert('Ürün başarıyla eklendi!');
                        closeModal('addProductModal');
                        loadProducts();
                        loadDashboard();
                    } else {
                        alert('Ürün eklenirken hata: ' + result.message);
                    }
                } else {
                    throw new Error('Ürün eklenemedi');
                }
            } catch (error) {
                console.error('Ürün ekleme hatası:', error);
                alert('Ürün eklenirken hata oluştu: ' + error.message);
            }
        });
        
        function editProduct(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;
            
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductDescription').value = product.description || '';
            document.getElementById('editProductPrice').value = product.price;
            document.getElementById('editProductStock').value = product.stock_quantity;
            document.getElementById('editProductCategory').value = product.category;
            document.getElementById('editProductBagType').value = product.bag_type || '';
            document.getElementById('editProductBagDimensions').value = product.bag_dimensions || '';
            document.getElementById('editProductMinOrderQuantity').value = product.min_order_quantity || '';
            document.getElementById('editProductBagDescription').value = product.bag_description || '';
            document.getElementById('editProductImage').value = product.image_url || '';
            document.getElementById('editProductCustom').checked = product.is_custom;
            
            document.getElementById('editProductModal').style.display = 'block';
        }
        
        document.getElementById('editProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const productData = Object.fromEntries(formData.entries());
            productData.is_custom = formData.has('is_custom');
            
            try {
                const response = await fetch(`api/admin_products.php?id=${productData.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(productData)
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        alert('Ürün başarıyla güncellendi!');
                        closeModal('editProductModal');
                        loadProducts();
                        loadDashboard();
                    } else {
                        alert('Ürün güncellenirken hata: ' + result.message);
                    }
                } else {
                    throw new Error('Ürün güncellenemedi');
                }
                    } catch (error) {
            console.error('Ürün güncelleme hatası:', error);
            alert('Ürün güncellenirken hata oluştu: ' + error.message);
        }
        });
        
        async function deleteProduct(productId) {
            if (!confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const response = await fetch(`api/admin_products.php?id=${productId}`, {
                    method: 'DELETE'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        alert('Ürün başarıyla silindi!');
                        loadProducts();
                        loadDashboard();
                    } else {
                        alert('Ürün silinirken hata: ' + result.message);
                    }
                } else {
                    throw new Error('Ürün silinemedi');
                }
            } catch (error) {
                console.error('Ürün silme hatası:', error);
                alert('Ürün silinirken hata oluştu: ' + error.message);
            }
        }
        
        // Order Management
        async function cancelOrder(orderId) {
            if (!confirm('Bu siparişi iptal etmek istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const response = await fetch(`api/admin_orders.php?id=${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: 'cancelled' })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        alert('Sipariş başarıyla iptal edildi!');
                        loadOrders();
                        loadDashboard();
                    } else {
                        alert('Sipariş iptal edilirken hata: ' + result.message);
                    }
                } else {
                    throw new Error('Sipariş iptal edilemedi');
                }
            } catch (error) {
                console.error('Sipariş iptal hatası:', error);
                alert('Sipariş iptal edilirken hata oluştu: ' + error.message);
            }
        }
        
        async function viewOrderDetails(orderId) {
            const order = orders.find(o => o.id === orderId);
            if (!order) return;
            
            const customer = customers.find(c => c.id === order.customer_id);
            
            const detailsHtml = `
                <div style="margin-bottom: 20px;">
                    <h4>Sipariş Bilgileri</h4>
                    <p><strong>Sipariş No:</strong> #${order.order_number}</p>
                    <p><strong>Durum:</strong> <span class="status-badge status-${order.status}">${getStatusText(order.status)}</span></p>
                    <p><strong>Tarih:</strong> ${new Date(order.created_at).toLocaleString('tr-TR')}</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4>Müşteri Bilgileri</h4>
                    <p><strong>Ad Soyad:</strong> ${customer ? customer.first_name + ' ' + customer.last_name : 'Bilinmiyor'}</p>
                    <p><strong>E-posta:</strong> ${customer ? customer.email : 'Bilinmiyor'}</p>
                    <p><strong>Telefon:</strong> ${customer ? customer.phone : 'Bilinmiyor'}</p>
                    <p><strong>Adres:</strong> ${customer ? customer.address : 'Bilinmiyor'}</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4>Ürün Bilgileri</h4>
                    <p><strong>Ürün Tipi:</strong> ${order.product_type}</p>
                    <p><strong>Renk:</strong> ${order.product_color}</p>
                    <p><strong>Boyut:</strong> ${order.product_size} cm</p>
                    <p><strong>Miktar:</strong> ${order.quantity}</p>
                    <p><strong>Birim Fiyat:</strong> ${order.unit_price}₺</p>
                    <p><strong>Toplam:</strong> ${order.total}₺</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4>Durum Güncelle</h4>
                    <select id="orderStatusSelect" style="padding: 8px; margin-right: 10px;">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Bekliyor</option>
                        <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Onaylandı</option>
                        <option value="production" ${order.status === 'production' ? 'selected' : ''}>Üretimde</option>
                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Kargoda</option>
                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Teslim Edildi</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>İptal Edildi</option>
                    </select>
                    <button class="btn-primary" onclick="updateOrderStatus(${order.id})">Güncelle</button>
                </div>
            `;
            
            document.getElementById('orderDetailsContent').innerHTML = detailsHtml;
            document.getElementById('orderDetailsModal').style.display = 'block';
        }
        
        async function updateOrderStatus(orderId) {
            const newStatus = document.getElementById('orderStatusSelect').value;
            
            try {
                const response = await fetch(`api/admin_orders.php?id=${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        alert('Sipariş durumu başarıyla güncellendi!');
                        closeModal('orderDetailsModal');
                        loadOrders();
                        loadDashboard();
                    } else {
                        alert('Durum güncellenirken hata: ' + result.message);
                    }
                } else {
                    throw new Error('Durum güncellenemedi');
                }
            } catch (error) {
                console.error('Durum güncelleme hatası:', error);
                alert('Durum güncellenirken hata oluştu: ' + error.message);
            }
        }
        
        // Utility functions
        function getStatusText(status) {
            const statusMap = {
                'pending': 'Bekliyor',
                'confirmed': 'Onaylandı',
                'production': 'Üretimde',
                'shipped': 'Kargoda',
                'delivered': 'Teslim Edildi',
                'cancelled': 'İptal Edildi'
            };
            return statusMap[status] || status;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboard();
        });
    </script>
</body>
</html>
