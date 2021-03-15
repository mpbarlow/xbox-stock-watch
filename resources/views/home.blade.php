<html lang="en">
  <head>
    <title>Xbox Stock Check</title>
  </head>
  <body>
    <div>
      <div style="display: flex; flex-direction: column; align-items: center;">
        {{ date('d/m/Y H:i:s', filemtime(public_path('/game.png'))) }}
        <img src="/game.png" style="max-width: 95%">
      </div>
      <div style="display: flex; flex-direction: column; align-items: center; margin-top: 1rem;">
        {{ date('d/m/Y H:i:s', filemtime(public_path('/smyths.png'))) }}
        <img src="/smyths.png" style="max-width: 95%">
      </div>
    </div>
  </body>
</html>