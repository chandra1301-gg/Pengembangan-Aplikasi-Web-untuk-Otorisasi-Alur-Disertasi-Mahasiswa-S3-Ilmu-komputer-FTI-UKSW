<?php
/**
 * File: includes/template_penilaian.php
 * Template penilaian untuk semua jenis ujian
 */

function getTemplatePenilaian($jenis_ujian) {
    $templates = [
        'proposal' => [
            ['aspek' => 'Sistematika dan Teknis Penulisan', 'bobot' => 10],
            ['aspek' => 'Kemampuan melakukan sintesis (kemampuan merangkum dan menganalisis berbagai pendapat, temuan dan atau teori)', 'bobot' => 15],
            ['aspek' => 'Kejelasan dan kedalaman Research/fenomena Gap', 'bobot' => 40],
            ['aspek' => 'Relevansi, otoritatif sumber dan kekinian Telaah Pustaka/Teori yang digunakan', 'bobot' => 20],
            ['aspek'=> 'Penguasaan Materi (presentasi dan tanya jawab)','bobot'=> 15]
        ],
        'kualifikasi' => [
            ['aspek' => 'Sistematika dan Teknis Penulisan', 'bobot' => 10],
            ['aspek' => 'Kemampuan melakukan sintesis (kemampuan merangkum dan menganalisis berbagai pendapat, temuan dan atau teori)', 'bobot' => 15],
            ['aspek' => 'Kejelasan dan kedalaman Research/fenomena Gap', 'bobot' => 40],
            ['aspek' => 'Relevansi, otoritatif sumber dan kekinian Telaah Pustaka/Teori yang digunakan', 'bobot' => 20],
            ['aspek'=> 'Penguasaan Materi (presentasi dan tanya jawab)','bobot'=> 15]
        ],
        'kelayakan' => [
            ['aspek' => 'Sistematika dan Teknis Penulisan', 'bobot' => 10],
            ['aspek' => 'Kemampuan melakukan sintesis (kemampuan merangkum dan menganalisis berbagai pendapat, temuan dan atau teori)', 'bobot' => 15],
            ['aspek' => 'Kejelasan dan kedalaman Research/fenomena Gap', 'bobot' => 40],
            ['aspek' => 'Relevansi, otoritatif sumber dan kekinian Telaah Pustaka/Teori yang digunakan', 'bobot' => 20],
            ['aspek'=> 'Penguasaan Materi (presentasi dan tanya jawab)','bobot'=> 15]
        ],
        'tertutup' => [
            ['aspek' => 'Sistematika dan Teknis Penulisan', 'bobot' => 10],
            ['aspek' => 'Kemampuan melakukan sintesis (kemampuan merangkum dan menganalisis berbagai pendapat, temuan dan atau teori)', 'bobot' => 15],
            ['aspek' => 'Kejelasan dan kedalaman Research/fenomena Gap', 'bobot' => 40],
            ['aspek' => 'Relevansi, otoritatif sumber dan kekinian Telaah Pustaka/Teori yang digunakan', 'bobot' => 20],
            ['aspek'=> 'Penguasaan Materi (presentasi dan tanya jawab)','bobot'=> 15]
        ]
    ];
    
    return $templates[$jenis_ujian] ?? $templates['proposal']; // default ke proposal
}

function getUjianTitle($jenis_ujian) {
    $titles = [
        'proposal' => 'UJIAN PROPOSAL',
        'kualifikasi' => 'UJIAN KUALIFIKASI',
        'kelayakan' => 'UJIAN KELAYAKAN',
        'tertutup' => 'UJIAN TERTUTUP'
    ];
    
    return $titles[$jenis_ujian] ?? 'UJIAN DISERTASI';
}

function getUjianDescription($jenis_ujian) {
    $descriptions = [
        'proposal' => 'Penilaian proposal',
        'kualifikasi' => 'Penilaian ujian kualifikasi',
        'kelayakan' => 'Penilaian ujian kelayakan',
        'tertutup' => 'Penilaian ujian tertutup'
    ];
    
    return $descriptions[$jenis_ujian] ?? 'Penilaian disertasi doktor';
}

function calculateGrade($nilai) {
    if ($nilai > 85) return 'A (Sangat Memuaskan)';
    if ($nilai > 80) return 'AB (Memuaskan)';
    if ($nilai >= 70) return 'B (Cukup Memuaskan)';
    return 'TIDAK LULUS (Perlu Perbaikan Signifikan)';
}

// PERBAIKAN: Uncomment fungsi clean_input
// function clean_input($data) {
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data);
//     return $data;
// }
?>