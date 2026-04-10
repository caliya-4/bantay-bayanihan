<?php
/**
 * Reusable Page Header Component
 * 
 * Usage:
 * <?php include '../includes/page-header.php'; ?>
 * 
 * Then in the HTML:
 * <div class="page-header">
 *     <h1><?= $pageTitle ?? 'Welcome' ?></h1>
 *     <p><?= $pageSubtitle ?? '' ?></p>
 * </div>
 * 
 * Or use the direct component:
 * <?php renderPageHeader($title, $subtitle, $gradient); ?>
 */

/**
 * Render a styled page header
 * @param string $title Main title
 * @param string $subtitle Subtitle text
 * @param string $gradient CSS gradient (default: primary)
 */
function renderPageHeader($title, $subtitle = '', $gradient = 'var(--gradient-primary)') {
    ?>
    <div class="page-header" style="background: <?= htmlspecialchars($gradient) ?>">
        <h1><?= htmlspecialchars($title) ?></h1>
        <?php if ($subtitle): ?>
            <p><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render stats cards grid
 * @param array $stats Array of stat items
 * 
 * Example:
 * $stats = [
 *     ['icon' => 'fas fa-exclamation-triangle', 'label' => 'Active Emergencies', 'value' => 5],
 *     ['icon' => 'fas fa-users', 'label' => 'Residents', 'value' => 120]
 * ];
 * renderStatsGrid($stats);
 */
function renderStatsGrid($stats = []) {
    if (empty($stats)) return;
    ?>
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="icon"><i class="<?= htmlspecialchars($stat['icon']) ?>"></i></div>
                <h3><?= htmlspecialchars($stat['label']) ?></h3>
                <h2><?= htmlspecialchars($stat['value']) ?></h2>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Render a card component
 * @param string $title Card title
 * @param string $content Card content HTML
 * @param string $icon Optional icon
 */
function renderCard($title, $content, $icon = '') {
    ?>
    <div class="card">
        <div class="card-header">
            <h3><?php if($icon): ?><i class="<?= htmlspecialchars($icon) ?>"></i><?php endif; ?> <?= htmlspecialchars($title) ?></h3>
        </div>
        <div class="card-body">
            <?= $content ?>
        </div>
    </div>
    <?php
}

/**
 * Render alert message
 * @param string $message Alert message
 * @param string $type Type: success, warning, error, info
 */
function renderAlert($message, $type = 'info') {
    $validTypes = ['success', 'warning', 'error', 'danger', 'info'];
    $type = in_array($type, $validTypes) ? $type : 'info';
    ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php
}

/**
 * Render badge
 * @param string $text Badge text
 * @param string $type Badge type: primary, success, warning, danger, info
 */
function renderBadge($text, $type = 'primary') {
    $validTypes = ['primary', 'success', 'warning', 'danger', 'error', 'info'];
    $type = in_array($type, $validTypes) ? $type : 'primary';
    ?>
    <span class="badge badge-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($text) ?></span>
    <?php
}

/**
 * Render button
 * @param string $text Button text
 * @param string $href Button link/href
 * @param string $type Button type: primary, secondary, success, danger, warning, info
 * @param string $icon Optional icon class
 * @param array $attributes Additional HTML attributes
 */
function renderButton($text, $href = '#', $type = 'primary', $icon = '', $attributes = []) {
    $validTypes = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
    $type = in_array($type, $validTypes) ? $type : 'primary';
    
    $attr = '';
    foreach ($attributes as $key => $value) {
        $attr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }
    ?>
    <a href="<?= htmlspecialchars($href) ?>" class="btn btn-<?= htmlspecialchars($type) ?>"<?= $attr ?>>
        <?php if ($icon): ?><i class="<?= htmlspecialchars($icon) ?>"></i><?php endif; ?>
        <?= htmlspecialchars($text) ?>
    </a>
    <?php
}
?>
