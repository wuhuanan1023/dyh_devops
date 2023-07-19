<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>登录页</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

</head>
<body>

<div id="app">{{ message }}</div>

<script>
    const { createApp, ref } = Vue

    createApp({
        setup() {
            const message = $name
            return {
                message
            }
        }
    }).mount('#app')
</script>

</body>
</html>
