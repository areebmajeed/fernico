<DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="noindex">
        <link href="{fernico_getAbsURL()}default/css/homeStyle.css" rel="stylesheet" type="text/css">
        <title>Welcome to Fernico</title>
    </head>
    <body>
    <div id="fernico-error">
        <h1>Welcome to Fernico</h1>
        <p>
            Hello <b>{$name}!</b>
        </p>

        <p>To get started, head over to <em>Fernico Documentation</em>. Making applications with Fernico is easy, fast and better.</p>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="What's your name?">
            <input type="submit" value="Greet Yourself">
        </form>

    </div>
    {themeFooter()}
    </body>
    </html>
