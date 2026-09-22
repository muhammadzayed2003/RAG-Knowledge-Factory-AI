<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>IntelliAgent</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --text: #17233f;
            --muted: #7d8aa5;
            --blue: #557cff;
            --cyan: #57d5ff;
            --violet: #9a79ff;
        }

        body {
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: var(--text);

            background:
                radial-gradient(circle at 8% 5%, rgba(84, 218, 255, .32), transparent 28%),
                radial-gradient(circle at 92% 10%, rgba(156, 118, 255, .27), transparent 30%),
                radial-gradient(circle at 55% 105%, rgba(83, 132, 255, .21), transparent 34%),
                linear-gradient(135deg, #fbfdff 0%, #eef6ff 45%, #f8f5ff 100%);

            overflow: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;

            background-image:
                linear-gradient(rgba(76, 118, 190, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(76, 118, 190, .045) 1px, transparent 1px);

            background-size: 46px 46px;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(8px);
            pointer-events: none;
        }

        .orb-one {
            width: 350px;
            height: 350px;
            left: -130px;
            top: -120px;

            background:
                radial-gradient(circle, rgba(92, 218, 255, .46), transparent 70%);
        }

        .orb-two {
            width: 430px;
            height: 430px;
            right: -170px;
            top: 120px;

            background:
                radial-gradient(circle, rgba(155, 119, 255, .34), transparent 70%);
        }

        .page {
            min-height: 100vh;

            position: relative;
            z-index: 2;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;
        }

        .chat-shell {
            width: 100%;
            max-width: 760px;

            height: min(
                660px,
                calc(100vh - 40px)
            );

            display: flex;
            flex-direction: column;

            border-radius: 27px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .84),
                    rgba(243, 248, 255, .60)
                );

            border: 1px solid rgba(255, 255, 255, .95);

            backdrop-filter: blur(30px);

            box-shadow:
                0 30px 80px rgba(63, 99, 150, .16),
                inset 0 1px 0 white;

            overflow: hidden;

            transform:
                perspective(1200px)
                rotateX(.25deg)
                rotateY(-.4deg);
        }

        .chat-header {
            flex: 0 0 auto;

            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 17px 19px;

            border-bottom:
                1px solid rgba(102, 138, 195, .12);

            background:
                rgba(255, 255, 255, .34);
        }

        .assistant {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .assistant-logo {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 14px;

            color: white;
            font-size: 19px;

            background:
                radial-gradient(
                    circle at 28% 18%,
                    #bdf7ff,
                    transparent 24%
                ),
                linear-gradient(
                    145deg,
                    #5479ff,
                    #5bd2f6 52%,
                    #9878ff
                );

            box-shadow:
                0 12px 26px rgba(76, 124, 231, .24),
                inset 0 1px 1px rgba(255, 255, 255, .75);
        }

        .assistant h1 {
            font-size: 14px;
        }

        .assistant p {
            margin-top: 3px;

            font-size: 9px;
            color: #8896ab;
        }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 7px;

            padding: 7px 10px;

            border-radius: 999px;

            color: #61728e;

            background:
                rgba(239, 247, 255, .86);

            border:
                1px solid rgba(112, 146, 202, .12);

            font-size: 8px;
        }

        .dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #35c795;

            box-shadow:
                0 0 12px rgba(53, 199, 149, .75);
        }

        .chat-messages {
            flex: 1;
            min-height: 0;

            display: flex;
            flex-direction: column;
            gap: 12px;

            overflow-y: auto;

            padding: 22px;
        }

        .hero {
            margin: auto;

            max-width: 510px;

            text-align: center;
        }

        .hero-visual {
            width: 100px;
            height: 100px;

            margin: 0 auto 18px;

            position: relative;

            display: grid;
            place-items: center;
        }

        .ring {
            position: absolute;

            border-radius: 50%;

            border:
                1px solid rgba(78, 128, 235, .19);
        }

        .ring.outer {
            width: 100px;
            height: 100px;

            animation:
                spin 11s linear infinite;
        }

        .ring.inner {
            width: 75px;
            height: 75px;

            border-style: dashed;

            animation:
                reverseSpin 8s linear infinite;
        }

        .core {
            width: 55px;
            height: 55px;

            display: grid;
            place-items: center;

            border-radius: 19px;

            color: white;
            font-size: 22px;

            background:
                linear-gradient(
                    145deg,
                    #577eff,
                    #5fd3f5 56%,
                    #987cff
                );

            box-shadow:
                0 18px 36px rgba(78, 125, 232, .26),
                inset 0 1px 1px white;

            transform:
                rotate(-8deg);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes reverseSpin {
            to {
                transform: rotate(-360deg);
            }
        }

        .hero h2 {
            font-size: 23px;

            letter-spacing: -.8px;

            background:
                linear-gradient(
                    90deg,
                    #2e426b,
                    #567eff,
                    #906ee3
                );

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            margin: 9px auto 0;

            max-width: 440px;

            color: #8492a8;

            font-size: 11px;
            line-height: 1.65;
        }

        .chat-message {
            width: 100%;
            display: flex;
        }

        .chat-message.user {
            justify-content: flex-end;
        }

        .chat-message.assistant-message {
            justify-content: flex-start;
        }

        .bubble {
            max-width: 78%;

            padding: 11px 14px;

            border-radius: 15px;

            font-size: 11px;
            line-height: 1.6;

            white-space: pre-wrap;
            word-break: break-word;
        }

        .user .bubble {
            color: white;

            background:
                linear-gradient(
                    135deg,
                    #557aff,
                    #59cff7 58%,
                    #9479ff
                );

            box-shadow:
                0 10px 24px rgba(72, 120, 225, .19);

            border-bottom-right-radius: 4px;
        }

        .assistant-message .bubble {
            color: #374b67;

            background:
                rgba(255, 255, 255, .84);

            border:
                1px solid rgba(110, 146, 203, .13);

            box-shadow:
                0 8px 20px rgba(73, 108, 158, .07);

            border-bottom-left-radius: 4px;
        }

        .sources {
            margin-top: 8px;
            padding-top: 7px;

            border-top:
                1px solid rgba(107, 139, 188, .12);

            color: #8391a6;

            font-size: 8px;
        }

        .typing {
            color: #71829b;
        }

        .chat-bottom {
            flex: 0 0 auto;

            padding: 14px 16px 16px;

            border-top:
                1px solid rgba(101, 137, 194, .12);

            background:
                rgba(255, 255, 255, .30);
        }

        .input-shell {
            display: flex;
            align-items: center;
            gap: 9px;

            padding:
                8px 8px 8px 15px;

            border-radius: 17px;

            background:
                rgba(255, 255, 255, .85);

            border:
                1px solid rgba(255, 255, 255, .97);

            box-shadow:
                0 13px 31px rgba(72, 107, 159, .10),
                inset 0 1px 0 white;
        }

        #chatInput {
            flex: 1;
            min-width: 0;

            border: 0;
            outline: 0;

            background: transparent;

            color: #334960;

            font-size: 11px;
        }

        #chatInput::placeholder {
            color: #9da9ba;
        }

        .send-button {
            width: 39px;
            height: 39px;

            display: grid;
            place-items: center;

            border: 0;

            border-radius: 12px;

            cursor: pointer;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #557aff,
                    #59cff6 58%,
                    #967aff
                );

            box-shadow:
                0 8px 19px rgba(74, 120, 225, .23);
        }

        .send-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .hint {
            margin-top: 8px;

            text-align: center;

            color: #a0aaba;

            font-size: 8px;
        }

        @media (max-width: 650px) {
            .page {
                padding: 10px;
            }

            .chat-shell {
                height: calc(100vh - 20px);
            }

            .status-pill {
                display: none;
            }

            .bubble {
                max-width: 90%;
            }
        }
    </style>
</head>

<body>

<div class="orb orb-one"></div>
<div class="orb orb-two"></div>

<div class="page">

    <main class="chat-shell">

        <header class="chat-header">

            <div class="assistant">

                <div class="assistant-logo">
                    ✦
                </div>

                <div>
                    <h1>IntelliAgent</h1>
                    <p>AI Knowledge Assistant</p>
                </div>

            </div>

            <div class="status-pill">
                <span class="dot"></span>
                Online
            </div>

        </header>

        <section
            id="chatMessages"
            class="chat-messages"
        >

            <div
                id="chatHero"
                class="hero"
            >

                <div class="hero-visual">

                    <div class="ring outer"></div>
                    <div class="ring inner"></div>

                    <div class="core">
                        ✦
                    </div>

                </div>

                <h2>
                    How can I help?
                </h2>

                <p>
                    Ask anything and IntelliAgent will provide
                    a clear and direct answer.
                </p>

            </div>

        </section>

        <footer class="chat-bottom">

            <div class="input-shell">

                <input
                    id="chatInput"
                    type="text"
                    placeholder="Type your message..."
                    autocomplete="off"
                >

                <button
                    id="sendButton"
                    class="send-button"
                    type="button"
                    onclick="sendMessage()"
                >
                    ↑
                </button>

            </div>

            <div class="hint">
                IntelliAgent
            </div>

        </footer>

    </main>

</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            document
                .getElementById('chatInput')
                .addEventListener(
                    'keydown',
                    function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            sendMessage();
                        }
                    }
                );
        }
    );

    async function sendMessage() {
        const input =
            document.getElementById('chatInput');

        const button =
            document.getElementById('sendButton');

        const question =
            input.value.trim();

        if (!question) {
            return;
        }

        removeHero();

        appendUserMessage(question);

        input.value = '';
        button.disabled = true;

        const typingId =
            appendTypingMessage();

        scrollChat();

        try {
            const response = await fetch(
                '/chatbot/public/ask',
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        question: question
                    })
                }
            );

            const data =
                await response.json();

            removeTypingMessage(
                typingId
            );

            if (!response.ok) {
                throw new Error(
                    data.error
                    || data.message
                    || 'Unable to generate answer.'
                );
            }

            appendAssistantMessage(
                data.answer,
                data.sources || []
            );

        } catch (error) {
            removeTypingMessage(
                typingId
            );

            appendAssistantMessage(
                `Error: ${error.message}`,
                []
            );

        } finally {
            button.disabled = false;

            input.focus();

            scrollChat();
        }
    }

    function appendUserMessage(text) {
        const container =
            document.getElementById(
                'chatMessages'
            );

        const wrapper =
            document.createElement('div');

        wrapper.className =
            'chat-message user';

        const bubble =
            document.createElement('div');

        bubble.className = 'bubble';

        bubble.textContent = text;

        wrapper.appendChild(
            bubble
        );

        container.appendChild(
            wrapper
        );
    }

    function appendAssistantMessage(
        text,
        sources = []
    ) {
        const container =
            document.getElementById(
                'chatMessages'
            );

        const wrapper =
            document.createElement('div');

        wrapper.className =
            'chat-message assistant-message';

        const bubble =
            document.createElement('div');

        bubble.className = 'bubble';

        const answer =
            document.createElement('div');

        answer.textContent = text;

        bubble.appendChild(
            answer
        );

        const unique =
            uniqueSourceFiles(
                sources
            );

        if (unique.length > 0) {
            const sourceBox =
                document.createElement(
                    'div'
                );

            sourceBox.className =
                'sources';

            sourceBox.textContent =
                `Sources: ${
                    unique
                        .map(
                            source =>
                                source.file_name
                        )
                        .join(', ')
                }`;

            bubble.appendChild(
                sourceBox
            );
        }

        wrapper.appendChild(
            bubble
        );

        container.appendChild(
            wrapper
        );
    }

    function appendTypingMessage() {
        const container =
            document.getElementById(
                'chatMessages'
            );

        const id =
            `typing-${Date.now()}`;

        const wrapper =
            document.createElement('div');

        wrapper.id = id;

        wrapper.className =
            'chat-message assistant-message';

        const bubble =
            document.createElement('div');

        bubble.className =
            'bubble typing';

        bubble.textContent =
            'Bringing chunks from Qdrant...';

        wrapper.appendChild(
            bubble
        );

        container.appendChild(
            wrapper
        );

        return id;
    }

    function removeTypingMessage(id) {
        const element =
            document.getElementById(id);

        if (element) {
            element.remove();
        }
    }

    function removeHero() {
        const hero =
            document.getElementById(
                'chatHero'
            );

        if (hero) {
            hero.remove();
        }
    }

    function scrollChat() {
        const container =
            document.getElementById(
                'chatMessages'
            );

        container.scrollTop =
            container.scrollHeight;
    }

    function uniqueSourceFiles(
        sources
    ) {
        const result = [];
        const seen = new Set();

        for (const source of sources) {
            const key =
                `${source.document_id}-${source.file_name}`;

            if (seen.has(key)) {
                continue;
            }

            seen.add(key);

            result.push(source);
        }

        return result;
    }
</script>

</body>
</html>