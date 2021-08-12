<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { 
                -webkit-transform: rotate(0deg); 
            }
            100% { 
                -webkit-transform: rotate(360deg); 
            }
        }

        @keyframes spin {
            0% { 
                transform: rotate(0deg); 
            }
            100% { 
                transform: rotate(360deg); 
            }
        }
        /* Style the navbar */
        #navbar {
            left: 75%;
        overflow: hidden;
        background-color: #333;
        padding: 5px;
        position: fixed;
        }

        /* Navbar links */
        #navbar a {
        float: right;
        display: block;
        color: #f2f2f2;
        text-align: center;
        padding: 14px;
        text-decoration: none;
        margin-right: 2px;
        }
        #navbar a:hover {
            background-color: white;
            color: black;
            border-radius: 5px;
        }

        .content {
        padding: 16px;
        }

        .sticky {
            position:fixed;
            left: 75%;
        }
} */
    </style>
    <!-- <title>Document</title> -->
</head>
<body>
<div id="navbar">
  <a href="#evaluation">Evaluation</a>
  <a href="#result">Result</a>
  <a href="#distance">Distance Manhattan</a>
  <a href="#dataset">Dataset</a>
</div>
<div class="content">
<!-- ===================================== HTML ======================================================== -->


<!-- ===================================== PHP ========================================================= -->
<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

// Memuat library
require_once 'tambahan/SimpleXLSX.php';
require_once 'tambahan/fixes.php';
require_once '../vendor/autoload.php';

use \HierarchicalClustering\Clustering;
use \HierarchicalClustering\Links\SingleLink;
use \HierarchicalClustering\Distances\ManhattanDistance;
use \HierarchicalClustering\Distances\EuclideanDistance;


//========= INI BAGIAN UPLOAD ====================
if(isset($_FILES['fileToUpload']['name'])) { // Untuk memastikan file telah di upload
    // file name
    $filename = $_FILES['fileToUpload']['name']; // Nama file yang sudah diupload ditampung ke dalam variable $filename
 
    // Location
    $location = 'uploads/'.$filename; // Lokasi penyimpanan file yang di tampung ke dalam variabel $location
 
    // file extension
    $file_extension = pathinfo($location, PATHINFO_EXTENSION);
    $file_extension = strtolower($file_extension);
    // statement di atas untuk mengetahui ekstensi file yang sudah di upload
    // dan di tampung ke dalam variable $file_extension
 
    // Validasi extensions
    $valid_ext = array("xls", "xlsx"); 
    // variable $valid_ext berisi array untuk validasi ekstensi apa saja yang diperbolehkan untuk diproses
 
    if(in_array($file_extension,$valid_ext)){ // Mengecek ekstensi sesuai syarat atau tidak
       // Upload file
       move_uploaded_file($_FILES['fileToUpload']['tmp_name'],$location);
       // Memindahkan file yang sudah di upload ke direktori penyimpanan   
    }
    else {
        echo "File harus berupa ekstensi .xls atau .xlsx";
        echo "<br><a href='index.html'>Kembali</a>";
        // Respon ketika ekstensi tidak sesuai syarat
        exit;
    }

    //============== LOADER ============================================
    echo "<div class='loader' id='loader'></div>";
    // Untuk menampilkan gambar loading

    // Load Excel
    // Validasi parse excel menjadi sebuah object yang berisi data2 berupa array
    if ( $xlsx = SimpleXLSX::parse($location) ) {
    } 
    else {
        echo SimpleXLSX::parseError();
    }

    // Filter data
    // Untuk seleksi data, mana data yang akan diproses, mana yang bukan
    $excelData = $xlsx->rows();
    $rowLength = count($excelData);
    $colLength = count(current($excelData));
    $input = array();
    for($i = 1; $i < $rowLength; $i++) {
        for($j = 2; $j < $colLength; $j++) {
            $input[$i-1][$j-2] = processData($xlsx->rows()[$i][$j]);
        }
    }

    //======================== Menampilkan dataset ========================
    // Untuk menampilkan dataset
    // Create a html table
    echo "<h1 id='dataset'>Dataset</h1>";
    echo "<table border='1' cellspacing='0' cellpadding='5'><tr>";

    // Membuat header table
    for($j = 0; $j < $colLength; $j++) {
        echo "<th>".$excelData[0][$j]."</th>";
    }
    echo "</tr>";

    // mengisi table
    for($i = 1; $i < $rowLength; $i++) {
        echo "<tr align='center'>";
        for($j = 0; $j < $colLength; $j++) {
            echo "<td>".$excelData[$i][$j]."</td>";
        }
        echo "</tr>";
    }

    // Table berhasil dibuat
    echo "</table>";

    //=================================== MEMULAI CLUSTERING ====================
    $object = new Clustering(
        $input, // data yang sudah diseleksi, transformasi dan siap untuk diproses
        new ManhattanDistance(), // Menggunakan jarak manhattan untuk penentuan cluster
        new SingleLink(), // Dan metode single linkage untuk cara pendekatannya (jarak yang terdekat)
        4 // Maksimal cluster
    );

    // mendapatkan banyaknya langkah clustering
    $levels = $object->getStepLevels();

    //================ Menampilkan matriks Distance manhattan ========================================
    echo "<h1 id='distance'>Distance Manhattan</h1>";
    echo "<table border='1' cellspacing='0' cellpadding='5'><tr>";

    $distances = $object->getDistances();
    // Membuat header
    echo "<th>Dman</th>";
    for($j = 0; $j < count($distances)+1; $j++) {
        echo "<th>".$excelData[$j+1][1]."</th>";
    }
    echo "</tr>";

    // mengisi tabel
    for($i = 0; $i < count($distances)+1; $i++) {
        echo "<tr align='center'>";
        echo "<th>".$excelData[$i+1][1]."</th>";
        for($j = 0; $j < count($distances)+1; $j++) {
            if($j > $i) {
                //echo "<br>";
                echo "<td>".(is_null($distances[$j][$i]) ? 0 : $distances[$j][$i])."</td>";
            }
            else if($j < $i)
                echo "<td>".$distances[$i][$j]."</td>";
            else
                echo "<td>0</td>";
        }
        echo "</tr>";
    }

    // Table berhasil dibuat
    echo "</table>";
    
    //=========================== Menampilkan hasil cluster ======================================
    echo "<h1 id='result'>Results</h1>";
    // untuk menyimpan data table yang sedang ditampilkan
    echo "<input type='hidden' name='curLevel' id='curLevel' value='table_".($levels-1)."'>";

    // Membuat opsi untuk memilih hasil step level ke berapa
    echo "<select name='level' id='levelOption' onchange='selectLevelChange()'>";
    for($i = 0; $i < $levels; $i++) {
        echo "<option value='table_$i'".($i == $levels-1 ? "selected" : "").">Level $i</option>";
    }
    echo "</select><br>";

    // Membuat table hasil per level (per langkah)
    for($i = 0; $i < $levels; $i++) {
        
        $clusters = $object->getCluster($i); // berisikan index element dr input array

        // Inisialisasi variabel untuk penomoran
        $clusterNum = 1; $counter = 0;

        // Membuat table hasil cluster per level
        echo "<div id='table_$i' style='display: ".($i != $levels-1 ? "none" : "block")."'>";
        echo "<table border='1' cellspacing='0' cellpadding='5'><tr><th>Cluster</th>";

        // Membuat header
        for($j = 1; $j < $colLength; $j++) {
            echo "<th>".$excelData[0][$j]."</th>";
        }
        echo "</tr>";

        // mengisi tabel
        foreach($clusters as $cluster) {
            $counter = 0;
            foreach($cluster as $result) {
                echo "<tr align='center'>";
                if($counter==0) echo "<td rowspan='".count($cluster)."'>$clusterNum</td>"; 
                for($j = 1; $j < $colLength; $j++) {
                    echo "<td>".processData($excelData[$result+1][$j])."</td>";
                }
                echo "</tr>";
                $counter++;
            }
            $clusterNum++;
        }

        // Tabel berhasil dibuat
        echo "</table>";
        
        // Memberikan jarak antar table
        echo "<br></div>";
    }
    
    
    //=========================== Menampilkan hasil evaluasi purity ======================================
    echo "<h1 id='evaluation'>Evaluation</h1>";
    // Untuk mendapatkan data cluster terakhir
    $clusters = $object->getCluster();
    // Mendapatkan hasil purity dengan memasukkan data cluster dan data yang diproses
    $purity = round(hitungPurity($clusters, $input), 2);
    echo "<p><b>Purity: $purity %</b></p>";
}
else // Kondisi bila tidak ada file yang di upload
    echo "Input tidak valid<br><a href='index.html'>Kembali</a>";
?>
<!-- ===================================== PHP ========================================================= -->

<!-- ===================================== HTML ======================================================== -->
    </div>
    <script>
        // Untuk menghilangkan loader bila proses sudah selesai
        let loader = document.getElementById("loader");
        loader.style.display = 'none';

        // fungsi untuk mengganti table saat mengganti select input
        function selectLevelChange() {
            var curLevel = document.getElementById('curLevel');
            var level = document.getElementById("levelOption");
            document.getElementById(level.value).style.display = "block";
            document.getElementById(curLevel.value).style.display = "none";
            console.log(curLevel.value+" "+level.value);
            curLevel.value = level.value;
        }

        // Event scroll untuk membawa tombol navigasi
        window.onscroll = function() {myStickyFunction()};

        // mendapatkan navbar object
        var navbar = document.getElementById("navbar");

        // Mendapatkan offset navbar
        var sticky = navbar.offsetTop;

        // fungsi untuk menambah class sticky pada kondisi tertentu
        function myStickyFunction() {
            if (window.pageYOffset >= sticky) {
                navbar.classList.add("sticky")
            } 
            else {
                navbar.classList.remove("sticky");
            }
        }
    </script>
</body>
</html>