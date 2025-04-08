<?php
require_once '../models/Menus.php';

if (isset($_GET['search'])) {
    $menus = new Menus();
    $searchTerm = $_GET['search'];
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 8; // Nombre d'éléments par page
    $offset = ($page - 1) * $limit;
    
    $menus->setNomplat($searchTerm);
    $data = $menus->searchMenu($searchTerm, $limit, $offset);
    
    $result = '';
    foreach ($data as $menu) {
        $imageData = !empty($menu['IMAGE']) ? 'data:image/jpeg;base64,' . base64_encode($menu['IMAGE']) : 'path/to/default-image.jpg';
        
        $result .= '<tr>';
        $result .= '<td>' . $menu['IDPLAT'] . '</td>';
        $result .= '<td>' . $menu['NOMPLAT'] . '</td>';
        $result .= '<td>' . $menu['PU'] . ' Ariary</td>';
        $result .= '<td><img src="' . $imageData . '" alt="image" width="50" height="50" class="rounded-circle"></td>';
        $result .= '<td>';
        $result .= '<a href="EditMenu.php?idplat=' . $menu['IDPLAT'] . '" class="btn btn-primary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg> Modifier</a> ';
        $result .= '<a href="DeleteMenu.php?idplat=' . $menu['IDPLAT'] . '" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg> Supprimer</a>';
        $result .= '</td>';
        $result .= '</tr>';
    }
    
    echo $result;
}
?>
