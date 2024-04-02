<?php

/** @var string $title */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        .container {
            width: 360px;
            padding: 8% 0 0;
            margin: auto;
        }
        .form {
            position: relative;
            max-width: 360px;
            margin: 0 auto 100px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
        }
        .form input {
            outline: 0;
            background: #f2f2f2;
            width: 100%;
            border: 0;
            margin: 0 0 10px;
            padding: 10px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form button {
            outline: 0;
            background: #ad150c;
            width: 100%;
            border: 0;
            padding: 10px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form">
            <form method="post">
                <input name="name" required type="text" placeholder="name"/>
                <input name="password" required type="password" placeholder="password"/>
                <button type="submit">sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
