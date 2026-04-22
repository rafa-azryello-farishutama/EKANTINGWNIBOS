<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link rel="stylesheet" href="dashboard.css">

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#295d12",
                        "second-primary": "#ffffff",
                        "input": "#f0f5fa",
                    }
                }
            }
        }
    </script>
</head>
<body class="flex bg-primary min-h-screen mt-[70px] justify-center">
    
    <div class="w-[475px] bg-second-primary rounded-[53px] flex flex-col h-screen items-center py-[30px] px-[30px] border-box">
        
        <div class="self-center w-[200px] bg-input h-[7px] rounded-[53px]"></div>

        <div class="flex flex-col items-center w-full mt-[8px]">
            <img src="assets/img/logoBaru1.png" class="w-[300px] h-auto">
            <img src="assets/img/welcome.png" class="w-[100px] h-auto">
        </div>

        <form method="POST" class="w-full m-0 p-0">
            <div class="flex flex-col gap-[10px] mt-[40px] w-full border-box">
                <div class="w-full h-[50px] bg-input rounded-[15px] flex items-center gap-[8px] border-box py-0 px-[15px]">
                    <img src="assets/img/Person.png" class="w-[20px] h-auto opacity-60">
                    <input type="text" name="username" class="border-none bg-transparent outline-none text-[20px] text-zinc-950 w-full
                        focus:ring-0 focus:outline-none focus:border-transparent">
                </div>

                <div class="w-full h-[50px] bg-input rounded-[15px] flex items-center gap-[8px] border-box py-0 px-[15px]">
                    <img src="assets/img/Key.png" class="w-[20px] h-auto opacity-60">
                    <input type="password" name="username" class="border-none bg-transparent outline-none text-[20px] text-zinc-950 w-full
                        focus:ring-0 focus:outline-none focus:border-transparent">
                </div>
            </div>

            <div class="w-full h-[50px] bg-primary rounded-[15px] flex items-center border-box py-0 px-[15px] mt-[20px]">
                <input type="submit" name="Submit"
                class="w-full no-underline bg-transparent outline-none border-none text-[15px] font-bold tracking-[2px] text-zinc-950 text-center
                cursor-pointer uppercase">
            </div>
        </form>

        <div class="flex justify-center items-center mt-[10px]">
            <a href="#" class="outline-none text-zinc-950">Lupa Sandi?</a>
        </div>
    </div>
</body>
</html>