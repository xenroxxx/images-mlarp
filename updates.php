<?php
// updates.php - Page d'affichage des mises à jour comme un blog
require_once 'config/config.php';
require_once 'functions.php';
require_once 'ban_check.php';
require_once 'vpn_check.php';

// Vérification explicite du ban d'IP
if (isIpBanned()) {
    header('Location: banned.php?reason=ip');
    exit();
}

// Vérification VPN
blockVPNUsers();

// Récupération des mises à jour
$updates_query = "SELECT * FROM base_updates ORDER BY created_at DESC";
$result = $conn->query($updates_query);
$updates = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row;
    }
}

// Si un ID d'update est spécifié, récupérer les détails de cette mise à jour
$single_update = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $update_id = (int)$_GET['id'];
    $single_query = "SELECT * FROM base_updates WHERE id = $update_id LIMIT 1";
    $single_result = $conn->query($single_query);
    
    if ($single_result && $single_result->num_rows > 0) {
        $single_update = $single_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $single_update ? htmlspecialchars($single_update['title']) . ' - FShop' : 'Mises à jour - FShop'; ?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://fshop.ovh/">
    <meta property="og:title" content="FShop - Solutions FiveM Premium">
    <meta property="og:description" content="Créez votre serveur GTA RP en toute simplicité ! Offrez une expérience de jeu premium et inoubliable à vos joueurs.">
    <meta property="og:image" content="https://fshop.ovh/assets/images/bannierebleu.png">
    
    <style>
        /* Styles spécifiques à la page des mises à jour */
        .updates-container {
            padding-top: 100px;
            padding-bottom: 50px;
        }
        
        .updates-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .updates-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .updates-header p {
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .updates-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .update-card {
            background: var(--dark-light);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .update-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }
        
        .update-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .update-title {
            font-size: 1.4rem;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .update-version {
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.7rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            flex-shrink: 0;
            margin-left: 0.5rem;
        }
        
        .update-date {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .update-excerpt {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .update-card .btn {
            align-self: flex-start;
            margin-top: auto;
        }
        
        /* Styles pour la vue détaillée d'une mise à jour */
        .single-update {
            background: var(--dark-light);
            border-radius: 10px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .single-update-header {
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1.5rem;
        }
        
        .single-update-title {
            font-size: 2rem;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .single-update-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .single-update-version {
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .single-update-date {
            color: var(--text-muted);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .single-update-content {
            line-height: 1.8;
            color: var(--text);
        }
        
        .single-update-content ul {
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .single-update-content li {
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .single-update-content h3 {
            color: var(--primary);
            margin: 1.5rem 0 1rem;
            font-size: 1.3rem;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary);
            transform: translateX(-5px);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        
        .pagination-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .pagination-item.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .updates-list {
                grid-template-columns: 1fr;
            }
            
            .single-update {
                padding: 1.5rem;
            }
            
            .single-update-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container updates-container">
        <?php if ($single_update): ?>
            <!-- Vue détaillée d'une mise à jour -->
            <a href="updates.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour à toutes les mises à jour
            </a>
            
            <div class="single-update">
                <div class="single-update-header">
                    <h1 class="single-update-title"><?php echo htmlspecialchars($single_update['title']); ?></h1>
                    <div class="single-update-meta">
                        <span class="single-update-version">v<?php echo htmlspecialchars($single_update['version']); ?></span>
                        <span class="single-update-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo date('d/m/Y', strtotime($single_update['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="single-update-content">
                    <?php echo nl2br(htmlspecialchars($single_update['content'])); ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Liste des mises à jour -->
            <div class="updates-header">
                <h1>Journal des mises à jour</h1>
                <p>Restez informé des dernières améliorations, corrections de bugs et nouvelles fonctionnalités de notre base FiveM.</p>
            </div>
            
            <?php if (empty($updates)): ?>
                <div class="no-content">
                    <h2>Aucune mise à jour disponible</h2>
                    <p>Les mises à jour seront bientôt publiées. Revenez plus tard !</p>
                </div>
            <?php else: ?>
                <div class="updates-list">
                    <?php foreach ($updates as $update): ?>
                        <div class="update-card">
                            <div class="update-header">
                                <h2 class="update-title"><?php echo htmlspecialchars($update['title']); ?></h2>
                                <span class="update-version">v<?php echo htmlspecialchars($update['version']); ?></span>
                            </div>
                            <div class="update-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d/m/Y', strtotime($update['created_at'])); ?>
                            </div>
                            <div class="update-excerpt">
                                <?php 
                                $excerpt = substr(strip_tags($update['content']), 0, 150);
                                echo htmlspecialchars($excerpt) . (strlen($update['content']) > 150 ? '...' : '');
                                ?>
                            </div>
                            <a href="updates.php?id=<?php echo $update['id']; ?>" class="btn btn-primary">Lire la suite</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination (à implémenter si beaucoup de mises à jour) -->
                <!--
                <div class="pagination">
                    <a href="#" class="pagination-item"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="pagination-item active">1</a>
                    <a href="#" class="pagination-item">2</a>
                    <a href="#" class="pagination-item">3</a>
                    <a href="#" class="pagination-item"><i class="fas fa-chevron-right"></i></a>
                </div>
                -->
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
