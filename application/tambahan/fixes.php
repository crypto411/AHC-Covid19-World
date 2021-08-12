<?php   
    // Fungssi untuk transformasi data
    function processData($data) {
        $data = str_replace(",", "", $data);
        $data = str_replace("+", "", $data);
        if($data == "N/A" || $data == null)
            $data = 0;
        return strval($data);
    }
    // fungsi untuk Mengambil data dari array yang dijadikan matriks
    function getDataMatriks(array $matriks, $i, $j){
        if($i > $j) return $matriks[$i][$j];
        if($i == $j) return 0;
        return $matriks[$j][$i];
    }

    // Fungsi untuk menghitung jumlah data class pada cluster tertentu
    function countDataClass(array $index, array $data, $class) {
        $indexCount = count($index);
        $colLength = count(current($data));
        $counter = 0;
        for($i = 0; $i < $indexCount; $i++) {
            if($data[$index[$i]][$colLength-1] == $class) {
                $counter++;
            } 
        }
        return $counter;
    }

    // Fungsi untuk menghitung purity
    function hitungPurity($clusters, $data) {
        $clasterCount = count($clusters);
        $dataCount = count($data);
        $dataColCount = count(current($data));
        $sumMax = 0;

        // Membuat table untuk pengelompokan class
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr rowspan='2'><th rowspan='2'></th><th colspan='4'>Travel Health Notice Level</th><tr>";
        echo "<tr><th>Cluster</th><th>Level 1</th><th>Level 2</th><th>Level 3</th><th>Level 4</th></tr>";
        for($k = 0; $k < $clasterCount; $k++) {
            echo "<tr><td>".($k+1)."</td>";
            $level_1 = countDataClass($clusters[$k], $data, 1);
            $level_2 = countDataClass($clusters[$k], $data, 2);
            $level_3 = countDataClass($clusters[$k], $data, 3);
            $level_4 = countDataClass($clusters[$k], $data, 4);
            echo "<td>$level_1</td><td>$level_2</td><td>$level_3</td><td>$level_4</td>";
            $max = max(array($level_1, $level_2, $level_3, $level_4));
            $sumMax += $max;
            echo "</tr>";
        }
        echo "</table>";

        // Rumus purity
        $purity = 1 / $dataCount * $sumMax;
        return $purity * 100;
    }
?>