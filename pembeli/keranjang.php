<?php 
$halaman = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "background": "#f7f8f9",
                        "primary": "#004900",
                        "second-primary": "#f9f9fb",
                        "input": "#f3f3f5",
                        "text-1": "#191c1c",
                        "text-2": "#4e5a48",
                        "text-3": "#5e6659",
                        "submit": "#005300"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-text-1 selection:bg-primary selection:text-text-2">
    <div class="flex min-h-screen relative">

        <?php include 'navbar.php'; ?>
    </div>
</body>
</html>