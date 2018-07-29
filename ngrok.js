const { spawn } = require('child_process')
const ngrok = require('ngrok')

const port = 8000

function serve () {
  return new Promise((resolve, reject) => {
    const cp = spawn('php', ['artisan', 'serve', '--port=' + port])
    cp.stdout.on('data', (data) => {
      const msg = data.toString('utf8')
      if (msg.startsWith('Laravel development server started')) {
        resolve()
      }
    })
  })
}

function install (url) {
  return new Promise((resolve, reject) => {
    const cp = spawn('php', ['artisan', 'telegram:install'], { env: { APP_URL: url } })
    cp.on('close', (code) => {
      if (0 == code) {
        resolve()
      } else {
        reject('Failed to install URL, return code: ' + code)
      }
    })
  })
}

function connect () {
  return new Promise((resolve, reject) => {
    ngrok.connect(port, (err, url) => {
      resolve(url)
    })
  })
}

async function start () {
  console.log('Serving application...')
  await serve()
  console.log('Done.')

  console.log('Connecting to ngrok...')
  const url = await connect()
  console.log('Done. Resolved URL: ' + url)

  console.log('Installing URL...')
  await install(url)
  console.log('Done.')
}

start()
