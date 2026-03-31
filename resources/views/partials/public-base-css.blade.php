        :root {
            --public-bg: #070707;
            --public-text: #f7f5ef;
        }
        * {
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--public-bg);
            color: var(--public-text);
            font-family: Inter, system-ui, sans-serif;
            line-height: 1.65;
            overflow-x: hidden;
        }
        .container {
            width: min(1140px, 92%);
            margin: 0 auto;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
