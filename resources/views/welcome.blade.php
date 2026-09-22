<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RAG AI Knowledge Factory</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --blue: #557cff;
            --cyan: #56d7ff;
            --violet: #9b74ff;
            --green: #35c795;
            --red: #d4576b;
            --text: #18243d;
            --muted: #8492a8;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;

            font-family:
                Inter,
                "Segoe UI",
                Arial,
                sans-serif;

            color: var(--text);

            background:
                radial-gradient(
                    circle at 5% 4%,
                    rgba(73,218,255,.35),
                    transparent 27%
                ),
                radial-gradient(
                    circle at 96% 10%,
                    rgba(157,108,255,.29),
                    transparent 29%
                ),
                radial-gradient(
                    circle at 53% 108%,
                    rgba(78,123,255,.22),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #fbfdff 0%,
                    #edf6ff 46%,
                    #faf6ff 100%
                );
        }

        body::before {
            content: "";

            position: fixed;
            inset: 0;

            z-index: -2;

            background-image:
                linear-gradient(
                    rgba(68,115,187,.045) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(68,115,187,.045) 1px,
                    transparent 1px
                );

            background-size: 42px 42px;

            transform:
                perspective(650px)
                rotateX(52deg)
                scale(1.7)
                translateY(23%);

            transform-origin:
                center bottom;

            pointer-events: none;
        }

        .ambient {
            position: fixed;

            border-radius: 50%;

            pointer-events: none;

            filter: blur(25px);

            z-index: -1;
        }

        .ambient.one {
            width: 390px;
            height: 390px;

            left: -170px;
            top: -130px;

            background:
                radial-gradient(
                    circle,
                    rgba(68,213,255,.41),
                    transparent 70%
                );
        }

        .ambient.two {
            width: 480px;
            height: 480px;

            right: -200px;
            top: 110px;

            background:
                radial-gradient(
                    circle,
                    rgba(155,113,255,.31),
                    transparent 70%
                );
        }

        .page {
            position: relative;

            z-index: 2;

            width: 100%;
            max-width: 1540px;

            margin: auto;

            padding: 26px;
        }

        .topbar {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 23px;
        }

        .brand {
            display: flex;

            align-items: center;

            gap: 14px;
        }

        .brand-cube {
            width: 53px;
            height: 53px;

            display: grid;

            place-items: center;

            position: relative;

            border-radius: 17px;

            color: white;

            font-size: 24px;

            background:
                radial-gradient(
                    circle at 26% 17%,
                    #d6fbff,
                    transparent 23%
                ),
                linear-gradient(
                    145deg,
                    #5075ff,
                    #55d2f6 54%,
                    #a275ff
                );

            box-shadow:
                13px 15px 29px
                    rgba(70,115,222,.22),
                inset 1px 1px 1px
                    rgba(255,255,255,.86);

            transform:
                perspective(600px)
                rotateX(10deg)
                rotateY(-15deg);
        }

        .brand-cube::after {
            content: "";

            position: absolute;

            left: 8px;
            right: 8px;
            bottom: -11px;

            height: 10px;

            border-radius: 50%;

            background:
                rgba(73,113,190,.18);

            filter: blur(7px);
        }

        .brand h1 {
            font-size: 21px;

            letter-spacing: -.6px;
        }

        .brand p {
            margin-top: 3px;

            color: var(--muted);

            font-size: 9px;

            letter-spacing: .3px;
        }

        .system-status {
            display: flex;

            align-items: center;

            gap: 8px;

            padding: 9px 13px;

            border-radius: 999px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.88),
                    rgba(239,247,255,.72)
                );

            border: 1px solid white;

            box-shadow:
                0 12px 28px
                    rgba(69,104,153,.10);

            color: #61728d;

            font-size: 9px;
        }

        .status-dot {
            width: 8px;
            height: 8px;

            border-radius: 50%;

            background: var(--green);

            box-shadow:
                0 0 13px
                    rgba(53,199,149,.8);
        }

        .dashboard {
            display: grid;

            grid-template-columns:
                minmax(350px, 400px)
                minmax(0, 1fr);

            gap: 23px;

            align-items: start;
        }

        .left-column,
        .right-column {
            display: flex;

            flex-direction: column;

            gap: 18px;
        }

        .glass {
            position: relative;

            border-radius: 24px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.85),
                    rgba(239,247,255,.60)
                );

            border:
                1px solid
                rgba(255,255,255,.96);

            backdrop-filter:
                blur(25px);

            box-shadow:
                18px 22px 60px
                    rgba(68,105,156,.12),
                -8px -8px 34px
                    rgba(255,255,255,.71),
                inset 1px 1px 1px
                    rgba(255,255,255,.96);

            overflow: hidden;
        }

        .glass::before {
            content: "";

            position: absolute;

            width: 190px;
            height: 190px;

            right: -100px;
            top: -110px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(85,210,255,.16),
                    transparent 69%
                );

            pointer-events: none;
        }

        .card {
            padding: 18px;
        }

        .section-title {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 14px;
        }

        .section-title h2 {
            font-size: 13px;

            letter-spacing: -.2px;
        }

        .pill {
            padding: 5px 8px;

            border-radius: 999px;

            background:
                rgba(234,243,255,.91);

            color: #7c8da7;

            font-size: 7px;
        }

        .factory {
            position: relative;

            min-height: 278px;

            padding: 17px;

            border-radius: 19px;

            overflow: hidden;

            background:
                radial-gradient(
                    circle at 50% 100%,
                    rgba(84,205,255,.18),
                    transparent 40%
                ),
                linear-gradient(
                    155deg,
                    rgba(247,252,255,.94),
                    rgba(230,242,255,.72)
                );

            border:
                1px solid
                rgba(103,151,214,.13);

            box-shadow:
                inset 0 1px 1px
                    rgba(255,255,255,.9),
                inset 0 -12px 40px
                    rgba(79,133,215,.04);
        }

        .factory-grid {
            position: absolute;

            left: -20%;
            right: -20%;
            bottom: -40px;

            height: 160px;

            opacity: .34;

            background-image:
                linear-gradient(
                    rgba(89,133,203,.17) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(89,133,203,.17) 1px,
                    transparent 1px
                );

            background-size:
                25px 25px;

            transform:
                perspective(320px)
                rotateX(66deg);

            transform-origin:
                center bottom;
        }

        .factory-machine {
            width: 125px;
            height: 94px;

            position: relative;

            margin:
                4px auto 17px;

            transform:
                perspective(620px)
                rotateX(7deg)
                rotateY(-10deg);
        }

        .machine-main {
            position: absolute;

            width: 86px;
            height: 71px;

            left: 20px;
            top: 10px;

            border-radius: 17px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.98),
                    rgba(214,231,253,.95)
                );

            border:
                1px solid
                rgba(92,137,211,.16);

            box-shadow:
                12px 16px 25px
                    rgba(69,110,171,.14),
                inset 2px 2px 4px white;
        }

        .machine-screen {
            position: absolute;

            width: 55px;
            height: 35px;

            left: 15px;
            top: 14px;

            display: grid;

            place-items: center;

            border-radius: 9px;

            color: white;

            font-size: 17px;

            background:
                radial-gradient(
                    circle at 25% 25%,
                    #93eaff,
                    transparent 29%
                ),
                linear-gradient(
                    145deg,
                    #4b6ff1,
                    #6fcff7 60%,
                    #8e73ed
                );

            box-shadow:
                inset 0 0 11px
                    rgba(255,255,255,.3),
                0 6px 16px
                    rgba(73,118,221,.18);
        }

        .machine-slot {
            position: absolute;

            width: 39px;
            height: 5px;

            left: 23px;
            bottom: 10px;

            border-radius: 5px;

            background: #abc1dd;
        }

        .machine-side {
            position: absolute;

            width: 25px;
            height: 57px;

            right: 2px;
            top: 19px;

            border-radius: 5px;

            background:
                linear-gradient(
                    145deg,
                    #d0def1,
                    #b8cae5
                );

            transform:
                skewY(-31deg);
        }

        .factory-node {
            position: absolute;

            width: 15px;
            height: 15px;

            border-radius: 4px;

            background:
                linear-gradient(
                    145deg,
                    #7ae3ff,
                    #6d7cff
                );

            box-shadow:
                0 7px 13px
                    rgba(80,120,215,.22);

            animation:
                nodeFloat 2.6s
                ease-in-out infinite;
        }

        .node-a {
            left: 9px;
            top: 34px;
        }

        .node-b {
            right: 7px;
            top: 7px;

            animation-delay: .5s;
        }

        .node-c {
            right: 13px;
            bottom: 1px;

            animation-delay: 1s;
        }

        @keyframes nodeFloat {
            0%,
            100% {
                transform:
                    translateY(0)
                    rotate(0deg);
            }

            50% {
                transform:
                    translateY(-8px)
                    rotate(8deg);
            }
        }

        .factory-heading {
            text-align: center;
        }

        .factory-heading strong {
            display: block;

            font-size: 13px;
        }

        .factory-heading span {
            display: block;

            margin-top: 5px;

            color: #8998ad;

            font-size: 8px;
            line-height: 1.5;
        }

        .input-control {
            width: 100%;

            margin-top: 11px;

            padding: 10px;

            border-radius: 11px;

            border:
                1px solid
                rgba(103,141,200,.13);

            outline: none;

            background:
                rgba(255,255,255,.72);

            color: #526782;

            font-size: 8px;
        }

        #documentFile {
            width: 100%;

            margin-top: 14px;

            padding: 8px;

            border-radius: 11px;

            background:
                rgba(255,255,255,.67);

            border:
                1px solid
                rgba(103,141,200,.13);

            color: #728199;

            font-size: 8px;
        }

        #documentFile::file-selector-button {
            border: 0;

            padding: 7px 9px;

            margin-right: 7px;

            border-radius: 8px;

            cursor: pointer;

            color: #4f70d8;

            font-weight: 700;
            font-size: 8px;

            background: #e9f1ff;
        }

        .primary-button {
            width: 100%;

            margin-top: 10px;

            padding: 11px;

            border: 0;
            border-radius: 11px;

            cursor: pointer;

            color: white;

            font-size: 9px;
            font-weight: 700;

            background:
                linear-gradient(
                    105deg,
                    #5277ff,
                    #57d0f8 55%,
                    #9875ff
                );

            box-shadow:
                0 12px 24px
                    rgba(72,116,225,.22);

            transition:
                transform .18s ease,
                box-shadow .18s ease;
        }

        .primary-button:hover {
            transform:
                translateY(-2px);

            box-shadow:
                0 16px 30px
                    rgba(72,116,225,.27);
        }

        .primary-button:disabled {
            opacity: .65;

            cursor: default;

            transform: none;
        }

        .ingestion-sequence {
            display: none;

            margin-top: 13px;

            padding: 13px;

            border-radius: 14px;

            background:
                linear-gradient(
                    145deg,
                    rgba(239,248,255,.92),
                    rgba(250,247,255,.88)
                );

            border:
                1px solid
                rgba(103,143,206,.12);
        }

        .ingestion-sequence.active {
            display: block;
        }

        .sequence-top {
            display: flex;

            justify-content: space-between;
            align-items: center;
        }

        .sequence-label {
            font-size: 8px;
            font-weight: 700;

            color: #526b91;
        }

        .sequence-time {
            font-size: 7px;

            color: #93a0b2;
        }

        .progress-shell {
            width: 100%;
            height: 7px;

            margin-top: 9px;

            border-radius: 999px;

            overflow: hidden;

            background:
                rgba(116,145,193,.11);
        }

        .progress-bar {
            width: 0%;
            height: 100%;

            border-radius: 999px;

            background:
                linear-gradient(
                    90deg,
                    #557aff,
                    #54d3fa,
                    #9974ff
                );

            box-shadow:
                0 0 12px
                    rgba(85,192,248,.4);

            transition:
                width .3s linear;
        }

        .stage-list {
            display: grid;

            grid-template-columns:
                repeat(5, 1fr);

            gap: 4px;

            margin-top: 10px;
        }

        .stage-list.six {
            grid-template-columns:
                repeat(6, 1fr);
        }

        .stage {
            min-height: 43px;

            display: flex;

            flex-direction: column;
            justify-content: center;

            padding: 5px;

            border-radius: 8px;

            text-align: center;

            color: #9ba7b8;

            background:
                rgba(255,255,255,.57);

            border:
                1px solid
                rgba(112,145,195,.08);

            font-size: 6px;

            transition:
                .25s ease;
        }

        .stage .stage-icon {
            display: block;

            margin-bottom: 3px;

            font-size: 11px;
        }

        .stage.active {
            color: #4f69ba;

            background:
                linear-gradient(
                    145deg,
                    #edf5ff,
                    #f2edff
                );

            border-color:
                rgba(83,124,235,.21);

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 14px
                    rgba(76,111,180,.09);
        }

        .stage.done {
            color: #248367;

            background: #effcf7;
        }

        .sequence-status {
            margin-top: 9px;

            min-height: 15px;

            text-align: center;

            color: #6b7d98;

            font-size: 8px;
        }

        .message {
            display: none;

            margin-top: 10px;

            padding: 8px 10px;

            border-radius: 9px;

            font-size: 8px;
        }

        .message.success {
            display: block;

            color: #24795e;

            background: #ebfff7;
        }

        .message.error {
            display: block;

            color: #c44f64;

            background: #fff0f3;
        }

        .documents-list,
        .websites-list {
            display: flex;

            flex-direction: column;

            gap: 8px;

            max-height: 290px;

            overflow-y: auto;
        }

        .knowledge-card {
            padding: 11px;

            border-radius: 13px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.88),
                    rgba(242,248,255,.72)
                );

            border:
                1px solid
                rgba(101,142,199,.11);

            box-shadow:
                0 8px 20px
                    rgba(76,109,158,.05);
        }

        .knowledge-header {
            display: flex;

            justify-content:
                space-between;

            gap: 8px;
        }

        .knowledge-name {
            font-size: 8px;

            font-weight: 700;

            word-break: break-word;
        }

        .knowledge-sub {
            margin-top: 3px;

            color: #9ba7b8;

            font-size: 6px;

            word-break: break-all;
        }

        .delete-button {
            border: 0;

            padding: 5px 7px;

            border-radius: 7px;

            cursor: pointer;

            color: #d05167;

            background: #fff1f4;

            font-size: 7px;
        }

        .knowledge-meta {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 5px;

            margin-top: 8px;
        }

        .meta-box {
            padding: 6px;

            border-radius: 8px;

            background:
                rgba(235,244,255,.72);
        }

        .meta-label {
            color: #a0abba;

            font-size: 5px;

            text-transform: uppercase;
        }

        .meta-value {
            margin-top: 2px;

            color: #57708c;

            font-size: 7px;
            font-weight: 700;
        }

        .ready {
            color: #23906b;
        }

        .processing {
            color: #b4832b;
        }

        .failed {
            color: #cf5367;
        }

        .empty {
            padding: 24px 10px;

            text-align: center;

            color: #9ca8b9;

            font-size: 8px;
        }

        .website-icon {
            width: 58px;
            height: 58px;

            display: grid;

            place-items: center;

            margin:
                2px auto 13px;

            border-radius: 19px;

            color: white;

            font-size: 22px;

            background:
                linear-gradient(
                    145deg,
                    #5277ff,
                    #56d5f5,
                    #9874ff
                );

            box-shadow:
                0 16px 30px
                    rgba(76,121,226,.22);

            animation:
                websitePulse
                3s ease-in-out infinite;
        }

        @keyframes websitePulse {
            50% {
                transform:
                    translateY(-5px)
                    rotate(3deg);

                box-shadow:
                    0 22px 38px
                        rgba(76,121,226,.28);
            }
        }

        .url-box {
            padding: 10px;

            border-radius: 11px;

            background:
                rgba(245,249,255,.83);

            border:
                1px solid
                rgba(100,139,201,.11);
        }

        .small-label {
            color: #929fb2;

            font-size: 6px;

            letter-spacing: .4px;

            text-transform: uppercase;
        }

        .url-row {
            display: flex;

            align-items: center;

            gap: 6px;

            margin-top: 6px;
        }

        .url-input {
            flex: 1;

            min-width: 0;

            border: 0;
            outline: 0;

            background: transparent;

            color: #526781;

            font-family:
                Consolas,
                monospace;

            font-size: 7px;

            white-space: nowrap;

            text-overflow: ellipsis;

            overflow: hidden;
        }

        .mini-button {
            border: 0;

            padding: 6px 8px;

            border-radius: 7px;

            cursor: pointer;

            color: #506cd0;

            background: #e9f0ff;

            font-size: 7px;
            font-weight: 700;
        }

        .api-name {
            width: 100%;

            margin-top: 9px;

            padding: 9px;

            border-radius: 9px;

            border:
                1px solid
                rgba(101,139,198,.13);

            outline: none;

            background:
                rgba(255,255,255,.76);

            font-size: 8px;
        }

        .generated {
            display: none;

            margin-top: 8px;
        }

        .generated.visible {
            display: block;
        }

        .api-list {
            display: flex;

            flex-direction: column;

            gap: 6px;

            max-height: 170px;

            margin-top: 10px;

            overflow-y: auto;
        }

        .api-key {
            padding: 8px;

            border-radius: 10px;

            background:
                rgba(255,255,255,.75);

            border:
                1px solid
                rgba(108,143,196,.09);
        }

        .api-key strong {
            font-size: 7px;
        }

        .key-prefix {
            margin-top: 3px;

            color: #8e9bae;

            font-family:
                Consolas,
                monospace;

            font-size: 6px;
        }

        .api-actions {
            display: flex;

            gap: 5px;

            margin-top: 6px;
        }

        .chat-card {
            height: 475px;

            display: flex;

            flex-direction: column;

            transform:
                perspective(1200px)
                rotateX(.35deg)
                rotateY(-.45deg);
        }

        .chat-header {
            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding: 14px 16px;

            border-bottom:
                1px solid
                rgba(102,139,195,.11);
        }

        .chat-brand {
            display: flex;

            align-items: center;

            gap: 9px;
        }

        .chat-logo {
            width: 36px;
            height: 36px;

            display: grid;

            place-items: center;

            border-radius: 12px;

            color: white;

            background:
                linear-gradient(
                    145deg,
                    #5579ff,
                    #5ed5f6,
                    #9877ff
                );

            box-shadow:
                0 9px 20px
                    rgba(75,119,224,.19);
        }

        .chat-brand h2 {
            font-size: 11px;
        }

        .chat-brand p {
            margin-top: 2px;

            color: #8e9aac;

            font-size: 7px;
        }

        .model {
            padding: 6px 8px;

            border-radius: 999px;

            color: #647591;

            background: #eff6ff;

            font-size: 6px;
        }

        .chat-messages {
            flex: 1;

            min-height: 0;

            display: flex;

            flex-direction: column;

            gap: 9px;

            padding: 17px;

            overflow-y: auto;
        }

        .chat-hero {
            margin: auto;

            text-align: center;
        }

        .chat-orb {
            width: 62px;
            height: 62px;

            display: grid;

            place-items: center;

            margin:
                0 auto 12px;

            border-radius: 20px;

            color: white;

            font-size: 23px;

            background:
                linear-gradient(
                    145deg,
                    #557bff,
                    #5ed7f4,
                    #9c78ff
                );

            box-shadow:
                0 17px 31px
                    rgba(82,126,230,.21);

            animation:
                hoverOrb
                3s ease-in-out infinite;
        }

        @keyframes hoverOrb {
            50% {
                transform:
                    translateY(-7px)
                    rotate(3deg);
            }
        }

        .chat-hero h3 {
            font-size: 19px;

            background:
                linear-gradient(
                    90deg,
                    #344969,
                    #557aff,
                    #906ee3
                );

            -webkit-background-clip:
                text;

            -webkit-text-fill-color:
                transparent;
        }

        .chat-hero p {
            margin-top: 6px;

            color: #8c99ac;

            font-size: 8px;
        }

        .chat-message {
            display: flex;

            width: 100%;
        }

        .chat-message.user {
            justify-content:
                flex-end;
        }

        .chat-message.assistant {
            justify-content:
                flex-start;
        }

        .bubble {
            max-width: 78%;

            padding: 9px 11px;

            border-radius: 12px;

            font-size: 9px;

            line-height: 1.55;

            white-space: pre-wrap;
        }

        .user .bubble {
            color: white;

            background:
                linear-gradient(
                    135deg,
                    #567aff,
                    #59cff7 58%,
                    #9479ff
                );

            border-bottom-right-radius:
                4px;
        }

        .assistant .bubble {
            color: #3f526d;

            background:
                rgba(255,255,255,.88);

            border:
                1px solid
                rgba(109,145,199,.11);

            border-bottom-left-radius:
                4px;
        }

        .source {
            margin-top: 6px;

            padding-top: 5px;

            border-top:
                1px solid
                rgba(103,138,189,.11);

            color: #8b98aa;

            font-size: 6px;
        }

        .chat-bottom {
            padding:
                11px 13px 13px;

            border-top:
                1px solid
                rgba(102,138,194,.11);
        }

        .chat-input-shell {
            display: flex;

            gap: 7px;

            padding:
                7px 7px 7px 12px;

            border-radius: 14px;

            background:
                rgba(255,255,255,.87);

            border: 1px solid white;
        }

        #chatInput {
            flex: 1;

            border: 0;
            outline: 0;

            background: transparent;

            color: #43566f;

            font-size: 9px;
        }

        .send {
            width: 34px;
            height: 34px;

            border: 0;

            border-radius: 10px;

            cursor: pointer;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #5479ff,
                    #5bd3f5,
                    #9479ff
                );
        }

        .api-example {
            padding: 15px;
        }

        pre {
            padding: 10px;

            border-radius: 11px;

            overflow-x: auto;

            color: #536981;

            background: #f1f7ff;

            font-size: 7px;

            line-height: 1.55;
        }

        @media (max-width: 1000px) {
            .dashboard {
                grid-template-columns:
                    1fr;
            }

            .chat-card {
                transform: none;
            }
        }

        @media (max-width: 580px) {
            .page {
                padding: 13px;
            }

            .system-status {
                display: none;
            }

            .stage-list,
            .stage-list.six {
                grid-template-columns:
                    repeat(
                        auto-fit,
                        minmax(48px, 1fr)
                    );
            }

            .chat-card {
                height: 520px;
            }
        }
    </style>
</head>

<body>

<div class="ambient one"></div>
<div class="ambient two"></div>

<div class="page">

    <header class="topbar">

        <div class="brand">

            <div class="brand-cube">
                ✦
            </div>

            <div>
                <h1>RAG AI Factory</h1>

                <p>
                    3D Intelligent Knowledge Infrastructure
                </p>
            </div>

        </div>

        <div class="system-status">

            <span class="status-dot"></span>

            Laravel · Gemini · Qdrant
        </div>

    </header>

    <div class="dashboard">

        <aside class="left-column">

            <!-- FILE INGESTION -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        Knowledge Ingestion Factory
                    </h2>

                    <span class="pill">
                        Factory 01
                    </span>

                </div>

                <div class="factory">

                    <div class="factory-grid"></div>

                    <div class="factory-machine">

                        <div
                            class="factory-node node-a"
                        ></div>

                        <div
                            class="factory-node node-b"
                        ></div>

                        <div
                            class="factory-node node-c"
                        ></div>

                        <div class="machine-main">

                            <div class="machine-screen">
                                ✦
                            </div>

                            <div class="machine-slot"></div>

                        </div>

                        <div class="machine-side"></div>

                    </div>

                    <div class="factory-heading">

                        <strong>
                            Feed Knowledge Into The Factory
                        </strong>

                        <span>
                            Documents, images, books and structured
                            files become searchable knowledge.
                        </span>

                    </div>

                    <input
                        id="documentFile"
                        type="file"
                        accept=".pdf,.docx,.txt,.md,.csv,.json,.xml,.html,.htm,.xlsx,.pptx,.jpg,.jpeg,.png,.webp"
                    >

                    <button
                        id="uploadButton"
                        class="primary-button"
                        onclick="uploadDocument()"
                    >
                        Start Knowledge Ingestion
                    </button>

                    <div
                        id="ingestionSequence"
                        class="ingestion-sequence"
                    >

                        <div class="sequence-top">

                            <span class="sequence-label">
                                AI Factory Pipeline
                            </span>

                            <span
                                id="sequenceTime"
                                class="sequence-time"
                            >
                                0%
                            </span>

                        </div>

                        <div class="progress-shell">

                            <div
                                id="progressBar"
                                class="progress-bar"
                            ></div>

                        </div>

                        <div class="stage-list">

                            <div
                                class="stage file-stage"
                                data-file-stage="0"
                            >
                                <span class="stage-icon">
                                    ◈
                                </span>

                                Extract
                            </div>

                            <div
                                class="stage file-stage"
                                data-file-stage="1"
                            >
                                <span class="stage-icon">
                                    ◫
                                </span>

                                Chunk
                            </div>

                            <div
                                class="stage file-stage"
                                data-file-stage="2"
                            >
                                <span class="stage-icon">
                                    ✦
                                </span>

                                Embed
                            </div>

                            <div
                                class="stage file-stage"
                                data-file-stage="3"
                            >
                                <span class="stage-icon">
                                    ⬡
                                </span>

                                Qdrant
                            </div>

                            <div
                                class="stage file-stage"
                                data-file-stage="4"
                            >
                                <span class="stage-icon">
                                    ✓
                                </span>

                                Ready
                            </div>

                        </div>

                        <div
                            id="sequenceStatus"
                            class="sequence-status"
                        >
                            Preparing factory...
                        </div>

                    </div>

                    <div
                        id="documentMessage"
                        class="message"
                    ></div>

                </div>

            </section>

            <!-- DOCUMENT KNOWLEDGE -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        Document Knowledge Base
                    </h2>

                    <span class="pill">
                        Files
                    </span>

                </div>

                <div
                    id="documentsList"
                    class="documents-list"
                >
                    <div class="empty">
                        Loading knowledge...
                    </div>
                </div>

            </section>

            <!-- WEBSITE FACTORY -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        Website Knowledge Factory
                    </h2>

                    <span class="pill">
                        Factory 02
                    </span>

                </div>

                <div class="factory">

                    <div class="factory-grid"></div>

                    <div class="website-icon">
                        ◎
                    </div>

                    <div class="factory-heading">

                        <strong>
                            Crawl A Website
                        </strong>

                        <span>
                            Discover public pages, extract their
                            knowledge, create chunks and store them
                            inside your vector knowledge base.
                        </span>

                    </div>

                    <input
                        id="websiteUrl"
                        class="input-control"
                        type="url"
                        placeholder="https://example.com"
                        autocomplete="off"
                    >

                    <input
                        id="websiteMaxPages"
                        class="input-control"
                        type="number"
                        min="1"
                        max="100"
                        value="25"
                        placeholder="Maximum pages"
                    >

                    <button
                        id="websiteButton"
                        class="primary-button"
                        onclick="crawlWebsite()"
                    >
                        Crawl & Ingest Website
                    </button>

                    <div
                        id="websiteSequence"
                        class="ingestion-sequence"
                    >

                        <div class="sequence-top">

                            <span class="sequence-label">
                                Website Crawler Pipeline
                            </span>

                            <span
                                id="websiteSequenceTime"
                                class="sequence-time"
                            >
                                0%
                            </span>

                        </div>

                        <div class="progress-shell">

                            <div
                                id="websiteProgressBar"
                                class="progress-bar"
                            ></div>

                        </div>

                        <div class="stage-list six">

                            <div
                                class="stage website-stage"
                                data-website-stage="0"
                            >
                                <span class="stage-icon">
                                    ◎
                                </span>

                                Connect
                            </div>

                            <div
                                class="stage website-stage"
                                data-website-stage="1"
                            >
                                <span class="stage-icon">
                                    ⇄
                                </span>

                                Discover
                            </div>

                            <div
                                class="stage website-stage"
                                data-website-stage="2"
                            >
                                <span class="stage-icon">
                                    ◈
                                </span>

                                Extract
                            </div>

                            <div
                                class="stage website-stage"
                                data-website-stage="3"
                            >
                                <span class="stage-icon">
                                    ◫
                                </span>

                                Chunk
                            </div>

                            <div
                                class="stage website-stage"
                                data-website-stage="4"
                            >
                                <span class="stage-icon">
                                    ✦
                                </span>

                                Embed
                            </div>

                            <div
                                class="stage website-stage"
                                data-website-stage="5"
                            >
                                <span class="stage-icon">
                                    ⬡
                                </span>

                                Store
                            </div>

                        </div>

                        <div
                            id="websiteSequenceStatus"
                            class="sequence-status"
                        >
                            Preparing crawler...
                        </div>

                    </div>

                    <div
                        id="websiteMessage"
                        class="message"
                    ></div>

                </div>

            </section>

            <!-- WEBSITE SOURCES -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        Website Knowledge
                    </h2>

                    <span class="pill">
                        Crawled Sources
                    </span>

                </div>

                <div
                    id="websitesList"
                    class="websites-list"
                >
                    <div class="empty">
                        Loading websites...
                    </div>
                </div>

            </section>

            <!-- PUBLIC CHAT URL -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        Public Chat URL
                    </h2>

                    <span class="pill">
                        Deployment
                    </span>

                </div>

                <div class="url-box">

                    <div class="small-label">
                        Public Browser Chat
                    </div>

                    <div class="url-row">

                        <input
                            id="publicChatUrl"
                            class="url-input"
                            value="{{ route('public.chat.show') }}"
                            readonly
                        >

                        <button
                            class="mini-button"
                            onclick="copyPublicChatUrl()"
                        >
                            Copy
                        </button>

                    </div>

                </div>

                <button
                    class="primary-button"
                    onclick="openPublicChat()"
                >
                    Open IntelliAgent
                </button>

                <div
                    id="publicMessage"
                    class="message"
                ></div>

            </section>

            <!-- API -->

            <section class="glass card">

                <div class="section-title">

                    <h2>
                        External API
                    </h2>

                    <span class="pill">
                        API Gateway
                    </span>

                </div>

                <div class="url-box">

                    <div class="small-label">
                        Chat Endpoint
                    </div>

                    <div class="url-row">

                        <input
                            id="endpointUrl"
                            class="url-input"
                            value="{{ url('/api/external/chat') }}"
                            readonly
                        >

                        <button
                            class="mini-button"
                            onclick="copyEndpoint()"
                        >
                            Copy
                        </button>

                    </div>

                </div>

                <input
                    id="apiKeyName"
                    class="api-name"
                    placeholder="API key name"
                >

                <button
                    class="primary-button"
                    onclick="generateApiKey()"
                >
                    Generate API Key
                </button>

                <div
                    id="generatedKeyBox"
                    class="url-box generated"
                >

                    <div class="small-label">
                        Generated API Key
                    </div>

                    <div class="url-row">

                        <input
                            id="generatedApiKey"
                            class="url-input"
                            readonly
                        >

                        <button
                            class="mini-button"
                            onclick="copyGeneratedKey()"
                        >
                            Copy
                        </button>

                    </div>

                </div>

                <div
                    id="apiMessage"
                    class="message"
                ></div>

                <div
                    id="apiKeysList"
                    class="api-list"
                ></div>

            </section>

        </aside>

        <main class="right-column">

            <!-- CHAT -->

            <section class="glass chat-card">

                <div class="chat-header">

                    <div class="chat-brand">

                        <div class="chat-logo">
                            ✦
                        </div>

                        <div>

                            <h2>
                                IntelliAgent
                            </h2>

                            <p>
                                Knowledge Intelligence
                            </p>

                        </div>

                    </div>

                    <div class="model">
                        Gemini 3.1 Flash-Lite
                    </div>

                </div>

                <div
                    id="chatMessages"
                    class="chat-messages"
                >

                    <div
                        id="chatHero"
                        class="chat-hero"
                    >

                        <div class="chat-orb">
                            ✦
                        </div>

                        <h3>
                            Ask IntelliAgent
                        </h3>

                        <p>
                            Documents and website knowledge
                            are ready to talk.
                        </p>

                    </div>

                </div>

                <div class="chat-bottom">

                    <div class="chat-input-shell">

                        <input
                            id="chatInput"
                            placeholder="Ask something..."
                            autocomplete="off"
                        >

                        <button
                            id="sendButton"
                            class="send"
                            onclick="sendMessage()"
                        >
                            ↑
                        </button>

                    </div>

                </div>

            </section>

            <!-- API EXAMPLE -->

            <section class="glass api-example">

                <div class="section-title">

                    <h2>
                        REST API Access
                    </h2>

                    <span class="pill">
                        Developer
                    </span>

                </div>

                <pre id="apiExample"></pre>

            </section>

        </main>

    </div>

</div>

<script>
    const csrfToken =
        '{{ csrf_token() }}';

    const publicChatUrl =
        @json(route('public.chat.show'));

    const externalApiUrl =
        @json(url('/api/external/chat'));

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            loadDocuments();
            loadWebsites();
            loadApiKeys();
            renderApiExample();

            document
                .getElementById('chatInput')
                .addEventListener(
                    'keydown',
                    function (event) {
                        if (
                            event.key
                            === 'Enter'
                        ) {
                            event.preventDefault();

                            sendMessage();
                        }
                    }
                );
        }
    );

    function delay(milliseconds) {
        return new Promise(
            resolve =>
                setTimeout(
                    resolve,
                    milliseconds
                )
        );
    }

    function renderApiExample() {
        document
            .getElementById(
                'apiExample'
            )
            .textContent =
`POST ${externalApiUrl}

Headers
Content-Type: application/json
X-API-Key: YOUR_API_KEY

Body
{
    "question": "Ask something"
}`;
    }

    /*
    |--------------------------------------------------------------------------
    | FILE INGESTION
    |--------------------------------------------------------------------------
    */

    async function uploadDocument() {
        const input =
            document.getElementById(
                'documentFile'
            );

        const button =
            document.getElementById(
                'uploadButton'
            );

        const file =
            input.files[0];

        if (!file) {
            showDocumentMessage(
                'Select a file first.',
                'error'
            );

            return;
        }

        hideDocumentMessage();

        button.disabled = true;

        button.textContent =
            'Knowledge Factory Running...';

        resetFileAnimation();

        const animationPromise =
            runFileAnimation();

        const formData =
            new FormData();

        formData.append(
            'file',
            file
        );

        const uploadPromise =
            fetch(
                '/documents',
                {
                    method: 'POST',

                    headers: {
                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken
                    },

                    body: formData
                }
            )
            .then(
                async response => {
                    const data =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.error
                            || data.message
                            || 'Document ingestion failed.'
                        );
                    }

                    return data;
                }
            );

        try {
            await Promise.all([
                uploadPromise,
                animationPromise
            ]);

            finishFileAnimation();

            input.value = '';

            showDocumentMessage(
                'Knowledge successfully processed and stored.',
                'success'
            );

            await loadDocuments();

        } catch (error) {
            failFileAnimation();

            showDocumentMessage(
                error.message,
                'error'
            );

        } finally {
            button.disabled = false;

            button.textContent =
                'Start Knowledge Ingestion';
        }
    }

    function resetFileAnimation() {
        const sequence =
            document.getElementById(
                'ingestionSequence'
            );

        sequence
            .classList
            .add('active');

        document
            .getElementById(
                'progressBar'
            )
            .style.width =
            '0%';

        document
            .getElementById(
                'sequenceTime'
            )
            .textContent =
            '0%';

        document
            .querySelectorAll(
                '.file-stage'
            )
            .forEach(
                stage => {
                    stage.classList.remove(
                        'active',
                        'done'
                    );
                }
            );

        document
            .getElementById(
                'sequenceStatus'
            )
            .textContent =
            'Document entering AI factory...';
    }

    async function runFileAnimation() {
        const stages = [
            'Extracting knowledge from file...',
            'Building intelligent chunks...',
            'Generating Gemini embeddings...',
            'Storing vectors inside Qdrant...',
            'Finalizing knowledge index...'
        ];

        const bar =
            document.getElementById(
                'progressBar'
            );

        const time =
            document.getElementById(
                'sequenceTime'
            );

        const status =
            document.getElementById(
                'sequenceStatus'
            );

        for (
            let index = 0;
            index < stages.length;
            index++
        ) {
            const element =
                document.querySelector(
                    `[data-file-stage="${index}"]`
                );

            element
                .classList
                .add('active');

            status.textContent =
                stages[index];

            const start =
                index * 20;

            const end =
                (index + 1) * 20;

            for (
                let percentage = start;
                percentage <= end;
                percentage += 2
            ) {
                bar.style.width =
                    percentage + '%';

                time.textContent =
                    percentage + '%';

                await delay(200);
            }

            element
                .classList
                .remove('active');

            element
                .classList
                .add('done');
        }

        status.textContent =
            'Waiting for knowledge engine...';
    }

    function finishFileAnimation() {
        document
            .getElementById(
                'progressBar'
            )
            .style.width =
            '100%';

        document
            .getElementById(
                'sequenceTime'
            )
            .textContent =
            '100%';

        document
            .querySelectorAll(
                '.file-stage'
            )
            .forEach(
                stage => {
                    stage.classList.add(
                        'done'
                    );
                }
            );

        document
            .getElementById(
                'sequenceStatus'
            )
            .textContent =
            'Knowledge Ready ✓';
    }

    function failFileAnimation() {
        document
            .getElementById(
                'sequenceStatus'
            )
            .textContent =
            'Knowledge ingestion failed.';
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT LIST
    |--------------------------------------------------------------------------
    */

    async function loadDocuments() {
        const list =
            document.getElementById(
                'documentsList'
            );

        try {
            const response =
                await fetch(
                    '/documents'
                );

            const data =
                await response.json();

            renderDocuments(
                data.documents
                || []
            );

        } catch {
            list.innerHTML =
                '<div class="empty">Unable to load documents.</div>';
        }
    }

    function renderDocuments(
        documents
    ) {
        const list =
            document.getElementById(
                'documentsList'
            );

        if (!documents.length) {
            list.innerHTML =
                '<div class="empty">Document knowledge base is empty.</div>';

            return;
        }

        list.innerHTML =
            documents.map(
                document => `
                    <div class="knowledge-card">

                        <div class="knowledge-header">

                            <div>

                                <div class="knowledge-name">
                                    ${escapeHtml(document.original_name)}
                                </div>

                                <div class="knowledge-sub">
                                    Knowledge ID ${document.id}
                                </div>

                            </div>

                            <button
                                class="delete-button"
                                onclick="deleteDocument(${document.id})"
                            >
                                Delete
                            </button>

                        </div>

                        <div class="knowledge-meta">

                            <div class="meta-box">

                                <div class="meta-label">
                                    Size
                                </div>

                                <div class="meta-value">
                                    ${formatFileSize(document.file_size)}
                                </div>

                            </div>

                            <div class="meta-box">

                                <div class="meta-label">
                                    Chunks
                                </div>

                                <div class="meta-value">
                                    ${document.chunk_count}
                                </div>

                            </div>

                            <div class="meta-box">

                                <div class="meta-label">
                                    Status
                                </div>

                                <div class="meta-value ${statusClass(document.status)}">
                                    ${escapeHtml(document.status)}
                                </div>

                            </div>

                        </div>

                    </div>
                `
            ).join('');
    }

    async function deleteDocument(id) {
        if (
            !confirm(
                'Delete this document and its vectors?'
            )
        ) {
            return;
        }

        try {
            const response =
                await fetch(
                    `/documents/${id}`,
                    {
                        method:
                            'DELETE',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken
                        }
                    }
                );

            const data =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    data.error
                    || data.message
                    || 'Unable to delete document.'
                );
            }

            await loadDocuments();

        } catch (error) {
            showDocumentMessage(
                error.message,
                'error'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | WEBSITE INGESTION
    |--------------------------------------------------------------------------
    */

    async function crawlWebsite() {
        const urlInput =
            document.getElementById(
                'websiteUrl'
            );

        const pagesInput =
            document.getElementById(
                'websiteMaxPages'
            );

        const button =
            document.getElementById(
                'websiteButton'
            );

        const url =
            urlInput.value.trim();

        const maxPages =
            Number(
                pagesInput.value
                || 25
            );

        if (!url) {
            showWebsiteMessage(
                'Enter a website URL first.',
                'error'
            );

            return;
        }

        if (
            maxPages < 1
            || maxPages > 100
        ) {
            showWebsiteMessage(
                'Maximum pages must be between 1 and 100.',
                'error'
            );

            return;
        }

        hideWebsiteMessage();

        button.disabled = true;

        button.textContent =
            'Website Factory Running...';

        resetWebsiteAnimation();

        const animationPromise =
            runWebsiteAnimation();

        const crawlerPromise =
            fetch(
                '/websites',
                {
                    method: 'POST',

                    headers: {
                        'Accept':
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken
                    },

                    body:
                        JSON.stringify({
                            url:
                                url,

                            max_pages:
                                maxPages
                        })
                }
            )
            .then(
                async response => {
                    const data =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.error
                            || data.message
                            || 'Website ingestion failed.'
                        );
                    }

                    return data;
                }
            );

        try {
            const results =
                await Promise.all([
                    crawlerPromise,
                    animationPromise
                ]);

            const result =
                results[0];

            finishWebsiteAnimation();

            showWebsiteMessage(
                `Website ready. ${result.website.page_count} pages and ${result.website.chunk_count} chunks stored.`,
                'success'
            );

            await loadWebsites();

        } catch (error) {
            failWebsiteAnimation();

            showWebsiteMessage(
                error.message,
                'error'
            );

        } finally {
            button.disabled =
                false;

            button.textContent =
                'Crawl & Ingest Website';
        }
    }

    function resetWebsiteAnimation() {
        document
            .getElementById(
                'websiteSequence'
            )
            .classList
            .add('active');

        document
            .getElementById(
                'websiteProgressBar'
            )
            .style.width =
            '0%';

        document
            .getElementById(
                'websiteSequenceTime'
            )
            .textContent =
            '0%';

        document
            .querySelectorAll(
                '.website-stage'
            )
            .forEach(
                stage => {
                    stage.classList.remove(
                        'active',
                        'done'
                    );
                }
            );

        document
            .getElementById(
                'websiteSequenceStatus'
            )
            .textContent =
            'Connecting to website...';
    }

    async function runWebsiteAnimation() {
        const stages = [
            {
                text:
                    'Connecting to website...',

                start: 0,
                end: 16
            },

            {
                text:
                    'Discovering public pages...',

                start: 16,
                end: 33
            },

            {
                text:
                    'Extracting page knowledge...',

                start: 33,
                end: 50
            },

            {
                text:
                    'Creating semantic chunks...',

                start: 50,
                end: 67
            },

            {
                text:
                    'Generating Gemini embeddings...',

                start: 67,
                end: 84
            },

            {
                text:
                    'Storing website vectors in Qdrant...',

                start: 84,
                end: 100
            }
        ];

        const bar =
            document.getElementById(
                'websiteProgressBar'
            );

        const time =
            document.getElementById(
                'websiteSequenceTime'
            );

        const status =
            document.getElementById(
                'websiteSequenceStatus'
            );

        for (
            let index = 0;
            index < stages.length;
            index++
        ) {
            const element =
                document.querySelector(
                    `[data-website-stage="${index}"]`
                );

            element
                .classList
                .add('active');

            status.textContent =
                stages[index].text;

            const span =
                stages[index].end
                - stages[index].start;

            const steps =
                8;

            for (
                let step = 0;
                step <= steps;
                step++
            ) {
                const value =
                    Math.round(
                        stages[index].start
                        + (
                            span
                            * step
                            / steps
                        )
                    );

                bar.style.width =
                    value + '%';

                time.textContent =
                    value + '%';

                await delay(220);
            }

            element
                .classList
                .remove('active');

            element
                .classList
                .add('done');
        }

        status.textContent =
            'Waiting for crawler engine...';
    }

    function finishWebsiteAnimation() {
        document
            .getElementById(
                'websiteProgressBar'
            )
            .style.width =
            '100%';

        document
            .getElementById(
                'websiteSequenceTime'
            )
            .textContent =
            '100%';

        document
            .querySelectorAll(
                '.website-stage'
            )
            .forEach(
                stage => {
                    stage.classList.add(
                        'done'
                    );
                }
            );

        document
            .getElementById(
                'websiteSequenceStatus'
            )
            .textContent =
            'Website Knowledge Ready ✓';
    }

    function failWebsiteAnimation() {
        document
            .getElementById(
                'websiteSequenceStatus'
            )
            .textContent =
            'Website ingestion failed.';
    }

    /*
    |--------------------------------------------------------------------------
    | WEBSITE LIST
    |--------------------------------------------------------------------------
    */

    async function loadWebsites() {
        const list =
            document.getElementById(
                'websitesList'
            );

        try {
            const response =
                await fetch(
                    '/websites'
                );

            const data =
                await response.json();

            renderWebsites(
                data.websites
                || []
            );

        } catch {
            list.innerHTML =
                '<div class="empty">Unable to load website knowledge.</div>';
        }
    }

    function renderWebsites(
        websites
    ) {
        const list =
            document.getElementById(
                'websitesList'
            );

        if (!websites.length) {
            list.innerHTML =
                '<div class="empty">No website has been ingested yet.</div>';

            return;
        }

        list.innerHTML =
            websites.map(
                website => `
                    <div class="knowledge-card">

                        <div class="knowledge-header">

                            <div>

                                <div class="knowledge-name">
                                    ${escapeHtml(website.host)}
                                </div>

                                <div class="knowledge-sub">
                                    ${escapeHtml(website.url)}
                                </div>

                            </div>

                            <button
                                class="delete-button"
                                onclick="deleteWebsite(${website.id})"
                            >
                                Delete
                            </button>

                        </div>

                        <div class="knowledge-meta">

                            <div class="meta-box">

                                <div class="meta-label">
                                    Pages
                                </div>

                                <div class="meta-value">
                                    ${website.page_count}
                                </div>

                            </div>

                            <div class="meta-box">

                                <div class="meta-label">
                                    Chunks
                                </div>

                                <div class="meta-value">
                                    ${website.chunk_count}
                                </div>

                            </div>

                            <div class="meta-box">

                                <div class="meta-label">
                                    Status
                                </div>

                                <div class="meta-value ${statusClass(website.status)}">
                                    ${escapeHtml(website.status)}
                                </div>

                            </div>

                        </div>

                    </div>
                `
            ).join('');
    }

    async function deleteWebsite(id) {
        if (
            !confirm(
                'Delete this website and all of its vectors?'
            )
        ) {
            return;
        }

        try {
            const response =
                await fetch(
                    `/websites/${id}`,
                    {
                        method:
                            'DELETE',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken
                        }
                    }
                );

            const data =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    data.error
                    || data.message
                    || 'Website deletion failed.'
                );
            }

            await loadWebsites();

        } catch (error) {
            showWebsiteMessage(
                error.message,
                'error'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHAT
    |--------------------------------------------------------------------------
    */

    async function sendMessage() {
        const input =
            document.getElementById(
                'chatInput'
            );

        const button =
            document.getElementById(
                'sendButton'
            );

        const question =
            input.value.trim();

        if (!question) {
            return;
        }

        removeChatHero();

        appendUserMessage(
            question
        );

        input.value = '';

        button.disabled = true;

        const typingId =
            appendTypingMessage();

        scrollChat();

        try {
            const response =
                await fetch(
                    '/chat',
                    {
                        method: 'POST',

                        headers: {
                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken
                        },

                        body:
                            JSON.stringify({
                                question
                            })
                    }
                );

            const data =
                await response.json();

            removeTyping(
                typingId
            );

            if (!response.ok) {
                throw new Error(
                    data.error
                    || data.message
                    || 'Unable to answer.'
                );
            }

            appendAssistantMessage(
                data.answer,
                data.sources
                || []
            );

        } catch (error) {
            removeTyping(
                typingId
            );

            appendAssistantMessage(
                'Error: '
                + error.message,
                []
            );

        } finally {
            button.disabled =
                false;

            scrollChat();
        }
    }

    function appendUserMessage(
        text
    ) {
        const container =
            document.getElementById(
                'chatMessages'
            );

        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.className =
            'chat-message user';

        const bubble =
            document.createElement(
                'div'
            );

        bubble.className =
            'bubble';

        bubble.textContent =
            text;

        wrapper.appendChild(
            bubble
        );

        container.appendChild(
            wrapper
        );
    }

    function appendAssistantMessage(
        text,
        sources
    ) {
        const container =
            document.getElementById(
                'chatMessages'
            );

        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.className =
            'chat-message assistant';

        const bubble =
            document.createElement(
                'div'
            );

        bubble.className =
            'bubble';

        const answer =
            document.createElement(
                'div'
            );

        answer.textContent =
            text;

        bubble.appendChild(
            answer
        );

        const unique =
            uniqueSources(
                sources
            );

        if (unique.length) {
            const source =
                document.createElement(
                    'div'
                );

            source.className =
                'source';

            source.textContent =
                'Sources: '
                + unique
                    .map(
                        item =>
                            item.file_name
                    )
                    .join(', ');

            bubble.appendChild(
                source
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
        const id =
            'typing-'
            + Date.now();

        const container =
            document.getElementById(
                'chatMessages'
            );

        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.id =
            id;

        wrapper.className =
            'chat-message assistant';

        const bubble =
            document.createElement(
                'div'
            );

        bubble.className =
            'bubble';

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

    function removeTyping(id) {
        const element =
            document.getElementById(
                id
            );

        if (element) {
            element.remove();
        }
    }

    function removeChatHero() {
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

    function uniqueSources(
        sources
    ) {
        const seen =
            new Set();

        return sources.filter(
            source => {
                const key =
                    source.document_id
                    + '-'
                    + source.file_name;

                if (
                    seen.has(key)
                ) {
                    return false;
                }

                seen.add(key);

                return true;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC CHAT
    |--------------------------------------------------------------------------
    */

    function openPublicChat() {
        window.open(
            publicChatUrl,
            '_blank'
        );
    }

    async function copyPublicChatUrl() {
        await copyText(
            publicChatUrl
        );

        showPublicMessage(
            'Public chat URL copied.',
            'success'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | API KEYS
    |--------------------------------------------------------------------------
    */

    async function loadApiKeys() {
        try {
            const response =
                await fetch(
                    '/api-keys'
                );

            const data =
                await response.json();

            renderApiKeys(
                data.keys
                || []
            );

        } catch {
            renderApiKeys([]);
        }
    }

    function renderApiKeys(keys) {
        const list =
            document.getElementById(
                'apiKeysList'
            );

        if (!keys.length) {
            list.innerHTML =
                '<div class="empty">No API keys.</div>';

            return;
        }

        list.innerHTML =
            keys.map(
                key => `
                    <div class="api-key">

                        <strong>
                            ${escapeHtml(key.name)}
                        </strong>

                        <div class="key-prefix">
                            ${escapeHtml(key.key_prefix)}••••••
                        </div>

                        <div class="api-actions">

                            <button
                                class="mini-button"
                                onclick="toggleApiKey(${key.id})"
                            >
                                ${
                                    key.is_active
                                    ? 'Disable'
                                    : 'Enable'
                                }
                            </button>

                            <button
                                class="delete-button"
                                onclick="deleteApiKey(${key.id})"
                            >
                                Delete
                            </button>

                        </div>

                    </div>
                `
            ).join('');
    }

    async function generateApiKey() {
        const name =
            document
                .getElementById(
                    'apiKeyName'
                )
                .value
                .trim()
            || 'External Chat API';

        try {
            const response =
                await fetch(
                    '/api-keys',
                    {
                        method:
                            'POST',

                        headers: {
                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken
                        },

                        body:
                            JSON.stringify({
                                name
                            })
                    }
                );

            const data =
                await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message
                    || 'Unable to create API key.'
                );
            }

            document
                .getElementById(
                    'generatedApiKey'
                )
                .value =
                data.api_key;

            document
                .getElementById(
                    'generatedKeyBox'
                )
                .classList
                .add('visible');

            await loadApiKeys();

        } catch (error) {
            showApiMessage(
                error.message,
                'error'
            );
        }
    }

    async function toggleApiKey(id) {
        await fetch(
            `/api-keys/${id}/toggle`,
            {
                method: 'PATCH',

                headers: {
                    'Accept':
                        'application/json',

                    'X-CSRF-TOKEN':
                        csrfToken
                }
            }
        );

        await loadApiKeys();
    }

    async function deleteApiKey(id) {
        if (
            !confirm(
                'Delete this API key?'
            )
        ) {
            return;
        }

        await fetch(
            `/api-keys/${id}`,
            {
                method:
                    'DELETE',

                headers: {
                    'Accept':
                        'application/json',

                    'X-CSRF-TOKEN':
                        csrfToken
                }
            }
        );

        await loadApiKeys();
    }

    async function copyEndpoint() {
        await copyText(
            externalApiUrl
        );

        showApiMessage(
            'Endpoint copied.',
            'success'
        );
    }

    async function copyGeneratedKey() {
        const key =
            document
                .getElementById(
                    'generatedApiKey'
                )
                .value;

        await copyText(
            key
        );

        showApiMessage(
            'API key copied.',
            'success'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    async function copyText(text) {
        await navigator
            .clipboard
            .writeText(
                text
            );
    }

    function showDocumentMessage(
        text,
        type
    ) {
        const box =
            document.getElementById(
                'documentMessage'
            );

        box.className =
            'message '
            + type;

        box.textContent =
            text;
    }

    function hideDocumentMessage() {
        const box =
            document.getElementById(
                'documentMessage'
            );

        box.className =
            'message';

        box.textContent =
            '';
    }

    function showWebsiteMessage(
        text,
        type
    ) {
        const box =
            document.getElementById(
                'websiteMessage'
            );

        box.className =
            'message '
            + type;

        box.textContent =
            text;
    }

    function hideWebsiteMessage() {
        const box =
            document.getElementById(
                'websiteMessage'
            );

        box.className =
            'message';

        box.textContent =
            '';
    }

    function showApiMessage(
        text,
        type
    ) {
        const box =
            document.getElementById(
                'apiMessage'
            );

        box.className =
            'message '
            + type;

        box.textContent =
            text;
    }

    function showPublicMessage(
        text,
        type
    ) {
        const box =
            document.getElementById(
                'publicMessage'
            );

        box.className =
            'message '
            + type;

        box.textContent =
            text;
    }

    function statusClass(status) {
        status =
            String(
                status
                || ''
            )
            .toLowerCase();

        if (
            status === 'ready'
        ) {
            return 'ready';
        }

        if (
            status === 'failed'
        ) {
            return 'failed';
        }

        return 'processing';
    }

    function formatFileSize(bytes) {
        const value =
            Number(bytes);

        if (
            value < 1024
        ) {
            return value
                + ' B';
        }

        if (
            value
            < 1024 * 1024
        ) {
            return (
                value / 1024
            ).toFixed(1)
            + ' KB';
        }

        return (
            value
            / 1024
            / 1024
        ).toFixed(1)
        + ' MB';
    }

    function escapeHtml(value) {
        const div =
            document.createElement(
                'div'
            );

        div.textContent =
            value
            ?? '';

        return div.innerHTML;
    }
</script>

</body>
</html>