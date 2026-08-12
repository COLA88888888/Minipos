<?php
if (!isset($base_path)) {
    $base_path = '';
}
?>
<!DOCTYPE html>
<html lang="lo">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ລະບົບບໍລິຫານການຂາຍ ແລະ ຄັງສິນຄ້າ Minimarket</title>
    
    <!-- Local Font - Noto Sans Lao Looped -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/local-font.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>ionicons-2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/global-custom.css?v=29">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Flatpickr Lao Datepicker CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/flatpickr.min.css">

    <!-- Core JavaScript Libraries (Load in head so all page scripts & components can use $ and Swal) -->
    <script src="<?php echo $base_path; ?>plugins/jquery/jquery.min.js"></script>
    <script src="<?php echo $base_path; ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_path; ?>plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <style>
      body {
        font-family: 'Noto Sans Lao Looped', sans-serif;
      }
      /* Custom Premium Scrollbar */
      ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #f1f1f1;
      }
      ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
      }
      
      /* Global preloader matching design */
      #global-preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.92);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.35s ease-out, visibility 0.35s ease-out;
        pointer-events: none;
      }
      #global-preloader.fade-out {
        opacity: 0;
        visibility: hidden;
      }
      .preloader-content {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
      }
      .lao-dots-spinner {
        display: inline-block;
        position: relative;
        width: 52px;
        height: 52px;
        margin-bottom: 14px;
      }
      .lao-dots-spinner div {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2563eb;
        animation: laoDotsPulse 0.9s linear infinite;
      }
      .lao-dots-spinner div:nth-child(1) { top: 2px; left: 21px; animation-delay: 0s; background: #60a5fa; }
      .lao-dots-spinner div:nth-child(2) { top: 7px; left: 33px; animation-delay: -0.112s; background: #93c5fd; }
      .lao-dots-spinner div:nth-child(3) { top: 21px; left: 39px; animation-delay: -0.225s; background: #bfdbfe; }
      .lao-dots-spinner div:nth-child(4) { top: 33px; left: 33px; animation-delay: -0.337s; background: #dbeafe; }
      .lao-dots-spinner div:nth-child(5) { top: 39px; left: 21px; animation-delay: -0.45s; background: #3b82f6; }
      .lao-dots-spinner div:nth-child(6) { top: 33px; left: 7px; animation-delay: -0.562s; background: #2563eb; }
      .lao-dots-spinner div:nth-child(7) { top: 21px; left: 2px; animation-delay: -0.675s; background: #1d4ed8; }
      .lao-dots-spinner div:nth-child(8) { top: 7px; left: 7px; animation-delay: -0.787s; background: #1e40af; }

      @keyframes laoDotsPulse {
        0%, 20%, 80%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(0.35); opacity: 0.2; }
      }

      .preloader-text {
        font-family: 'Noto Sans Lao Looped', 'Noto Sans Lao', sans-serif;
        font-size: 1.05rem;
        color: #334155;
        font-weight: 600;
      }
    </style>
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <!-- Fast 8-Dots Circular Global Preloader -->
    <div id="global-preloader">
        <div class="preloader-content">
            <div class="lao-dots-spinner">
              <div></div><div></div><div></div><div></div>
              <div></div><div></div><div></div><div></div>
            </div>
            <span class="preloader-text">ກຳລັງໂຫຼດຂໍ້ມູນ...</span>
        </div>
    </div>
