<?php
    include_once('simple_html_dom.php');
    
    // URL de la página a analizar
    $url = 'https://myanimelist.net/anime/season';

    // Realizar la solicitud GET a la URL y obtener el contenido HTML
    $html = file_get_html($url);

    // Encontrar el contenedor principal de los animes
    $anime_list = $html->find('div.seasonal-anime-list.js-seasonal-anime-list.js-seasonal-anime-list-key-1', 0);

    // Crear una lista vacía para almacenar los datos de los animes
    $animes = array();

    // Recorrer todos los elementos "div" con clase "title"
    foreach ($anime_list->find('div.title') as $i => $div) {

        // Obtener el nombre del anime
        $anime_name = $div->find('a.link-title', 0)->plaintext;
    
        // Obtener la fecha de lanzamiento
        $release_date_str = $div->find('span.js-start_date', 0)->plaintext;
    
        // Convertir la fecha de lanzamiento a un objeto DateTime
        try {
            $release_date = DateTime::createFromFormat('Ymd', substr($release_date_str, 0, 8));
        } catch (Exception $e) {
            $release_date = null;
        }
    
        // Obtener la puntuación y el número de miembros
        $score = $div->find('span.js-score', 0)->plaintext;
        $members = $div->find('span.js-members', 0)->plaintext;
    
        // Obtener la URL de la imagen
        $div_image = $anime_list->find('div.image', $i);
        $img = $div_image->find('img', 0);
        if ($img) {
            $img_src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            $img_alt = $img->getAttribute('alt');
            $img_href = $div_image->find('a', 0)->getAttribute('href');
        } else {
            echo "Image not found.<br>";
        }
    
        // Agregar el nombre del anime, la fecha de lanzamiento, la puntuación, el número de miembros y la URL de la imagen a la lista de animes
        $animes[] = array('name' => $anime_name, 'release_date' => $release_date, 'score' => $score, 'members' => $members, 'img_url' => $img_src, 'img_alt' => $img_alt, 'img_href_tag' => $img_href,);
    }

    // Eliminar los animes sin fecha de lanzamiento
    $animes = array_filter($animes, function($anime) {
        return $anime['release_date'] !== null;
    });

    // Ordenar los animes por fecha de lanzamiento
    usort($animes, function($a, $b) {
        return $a['release_date'] <=> $b['release_date'];
    });

    $spanish_days = array(
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    );
    
    // Crear una tabla que muestre los animes organizados por día de la semana
    $table_rows = array();
    foreach (array_keys($spanish_days) as $day) {
        $anime_rows = array_filter($animes, function($anime) use ($day) {
            return $anime['release_date']->format('l') === $day;
        });
    
        $table_row = '<tr><td>' . $spanish_days[$day] . '</td><td>';
        foreach ($anime_rows as $anime) {
            $table_row .= '<div class="media"><div class="media-left"><a href="' . $anime['img_href_tag'] . '"></a><img class="media-object animated img-zoom" alt="' . $anime['img_alt'] . '" src="' . $anime['img_url'] . '" width="100" height="140"></a></div><div class="media-body"><h4 class="media-heading">' . $anime['name'] . '</h4><p>Score: ' . $anime['score'] . '</p><p>Members: ' . $anime['members'] . '</p><p>Date: ' . DateTime::createFromFormat('Y-m-d', $anime['release_date']->format('Y-m-d'))->format('d-m-Y') . '</p></div></div>';
        }
        $table_row .= '</td></tr>';
        $table_rows[] = $table_row;
    }
    
    // Generar la tabla en HTML con Bootstrap
    $table_html = '<table class="table"><thead><tr><th>Día de la semana</th><th>Animes</th></tr></thead><tbody>' . implode($table_rows) . '</tbody></table>';    
?>

<style>
    .img-zoom {
        transition: transform .2s; /* Agrega transición suave */
    }

    .img-zoom:hover {
        transform: scale(1.1); /* Agranda la imagen al 110% en hover */
    }
</style>

<!DOCTYPE html>
<html>
<head>
    <title>Animes de la temporada</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
        }
        table {
            background-color: #2c2c2c;
            color: #fff;
        }
        th {
            background-color: #404040;
            color: #fff;
        }
        td {
            background-color: #1a1a1a;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <?php echo $table_html; ?>
    </div>
  </div>
</div>
</body>
</html>


