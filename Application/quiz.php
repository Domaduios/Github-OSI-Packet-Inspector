<?php
include 'auth_check.php';
include 'config.php';
$activeTab = 'quiz';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OSI Quiz — OSI Inspector</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .quiz-card {
            max-width: 720px;
            margin: 0 auto;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .quiz-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-family: var(--mono);
            color: var(--text-muted);
            font-weight: 600;
        }

        .progress-bar {
            position: relative;
            height: 4px;
            background: var(--border);
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #8b5cf6);
            transition: width .3s;
        }

        .question-area { padding: 30px; }

        .question-num {
            font-size: 11px;
            color: var(--text-muted);
            font-family: var(--mono);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .question-text {
            font-size: 18px;
            font-weight: 600;
            line-height: 1.5;
            margin-bottom: 22px;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .option {
            background: var(--bg-elevated);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            transition: all .12s;
        }
        .option:hover { border-color: var(--primary); background: var(--bg-hover); }
        .option.selected { border-color: var(--primary); background: var(--primary-bg); color: var(--primary); font-weight: 600; }
        .option.correct { border-color: var(--success); background: var(--success-bg); color: var(--success); font-weight: 600; }
        .option.wrong   { border-color: var(--danger); background: var(--danger-bg); color: var(--danger); font-weight: 600; }
        .option.disabled { pointer-events: none; }

        .option-letter {
            width: 26px; height: 26px;
            border-radius: 6px;
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            font-family: var(--mono);
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
        }
        .option.selected .option-letter { background: var(--primary); color: white; border-color: var(--primary); }
        .option.correct .option-letter { background: var(--success); color: white; border-color: var(--success); }
        .option.wrong .option-letter { background: var(--danger); color: white; border-color: var(--danger); }

        .explanation {
            background: var(--info-bg);
            border-left: 3px solid var(--info);
            border-radius: var(--radius);
            padding: 14px 16px;
            font-size: 13px;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .explanation::before {
            content: '💡 Explanation';
            display: block;
            font-size: 11px;
            color: var(--info);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 6px;
        }

        .quiz-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 18px 20px;
            background: var(--bg-sidebar);
            border-top: 1px solid var(--border);
        }

        /* Results */
        .results {
            text-align: center;
            padding: 50px 30px;
        }
        .score-circle {
            width: 140px; height: 140px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: conic-gradient(var(--success) var(--score), var(--border) 0);
            display: grid;
            place-items: center;
            position: relative;
        }
        .score-circle::before {
            content: '';
            position: absolute;
            inset: 12px;
            background: var(--bg-elevated);
            border-radius: 50%;
        }
        .score-text {
            position: relative;
            font-size: 40px;
            font-weight: 800;
            font-family: var(--mono);
        }
        .score-pass { color: var(--success); }
        .score-fail { color: var(--danger); }

        .badge-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            font-family: var(--mono);
            letter-spacing: .5px;
        }
        .difficulty-Easy { background: var(--success-bg); color: var(--success); }
        .difficulty-Medium { background: var(--warning-bg); color: var(--warning); }
        .difficulty-Hard { background: var(--danger-bg); color: var(--danger); }
    </style>
</head>
<body>

<div class="app-shell">
    <?php include '_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Knowledge Quiz</div>
                <div class="topbar-sub">Test your OSI & networking knowledge</div>
            </div>
            <div class="topbar-actions">
                <button class="btn" onclick="restart()">↻ Restart</button>
            </div>
        </div>

        <div class="content">
            <div class="quiz-card fade-in" id="quizCard">
                <!-- Will be populated by JS -->
                <div class="text-center" style="padding:60px;color:var(--text-muted);">Loading questions…</div>
            </div>
        </div>
    </main>
</div>

<script>
let questions = [];
let currentIdx = 0;
let answers = {};

async function loadQuiz() {
    try {
        const res = await fetch('api.php?action=getQuizQuestions');
        const data = await res.json();
        if (!data.success) return;
        questions = data.questions;
        currentIdx = 0;
        answers = {};
        renderQuestion();
    } catch (e) { console.error(e); }
}

function renderQuestion() {
    if (currentIdx >= questions.length) { renderResults(); return; }

    const q = questions[currentIdx];
    const pct = ((currentIdx) / questions.length) * 100;
    const userAnswer = answers[q.QuestionID];

    document.getElementById('quizCard').innerHTML = `
        <div class="quiz-progress">
            <span>Question ${currentIdx + 1} / ${questions.length}</span>
            <span class="badge-tag difficulty-${q.Difficulty}">${q.Difficulty} · ${q.Topic}</span>
        </div>
        <div class="progress-bar"><div class="progress-fill" style="width:${pct}%"></div></div>

        <div class="question-area">
            <div class="question-num">QUESTION ${currentIdx + 1}</div>
            <div class="question-text">${q.Question}</div>

            <div class="options-list">
                ${['A','B','C','D'].map(letter => {
                    const opt = q['Option' + letter];
                    let cls = '';
                    if (userAnswer) {
                        cls = 'disabled ';
                        if (letter === q.CorrectAnswer) cls += 'correct';
                        else if (letter === userAnswer) cls += 'wrong';
                    }
                    return `<div class="option ${cls}" onclick="selectAnswer('${letter}')">
                        <div class="option-letter">${letter}</div>
                        <div>${opt}</div>
                    </div>`;
                }).join('')}
            </div>

            ${userAnswer ? `<div class="explanation">${q.Explanation}</div>` : ''}
        </div>

        <div class="quiz-actions">
            <button class="btn" ${currentIdx === 0 ? 'disabled' : ''} onclick="prev()">← Previous</button>
            <button class="btn btn-primary" onclick="next()">${currentIdx === questions.length - 1 ? 'Finish ✓' : 'Next →'}</button>
        </div>
    `;
}

function selectAnswer(letter) {
    const q = questions[currentIdx];
    if (answers[q.QuestionID]) return;
    answers[q.QuestionID] = letter;
    renderQuestion();
}

function next() { currentIdx++; renderQuestion(); }
function prev() { if (currentIdx > 0) { currentIdx--; renderQuestion(); } }

function renderResults() {
    let correct = 0;
    questions.forEach(q => { if (answers[q.QuestionID] === q.CorrectAnswer) correct++; });
    const total = questions.length;
    const pct = Math.round((correct / total) * 100);
    const passed = pct >= 60;

    const wrongQuestions = questions.filter(q => answers[q.QuestionID] !== q.CorrectAnswer);

    document.getElementById('quizCard').innerHTML = `
        <div class="results">
            <div class="score-circle" style="--score: ${pct * 3.6}deg">
                <div class="score-text ${passed ? 'score-pass' : 'score-fail'}">${pct}%</div>
            </div>
            <h2 style="font-size:22px;font-weight:700;margin-bottom:8px;">
                ${passed ? '🎉 Well done!' : '📚 Keep learning!'}
            </h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
                You answered <strong>${correct}</strong> out of <strong>${total}</strong> correctly
            </p>
            ${wrongQuestions.length > 0 ? `
                <div style="text-align:left;max-width:540px;margin:20px auto;">
                    <h3 style="font-size:13px;color:var(--text-muted);margin-bottom:10px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
                        Review (${wrongQuestions.length} wrong):
                    </h3>
                    ${wrongQuestions.map(q => `
                        <div style="background:var(--bg-sidebar);padding:12px 14px;border-radius:8px;margin-bottom:8px;font-size:13px;">
                            <div style="margin-bottom:4px;">${q.Question}</div>
                            <div style="color:var(--success);font-size:12px;font-weight:600;">✓ ${q['Option' + q.CorrectAnswer]}</div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
            <div style="display:flex;gap:8px;justify-content:center;margin-top:20px;">
                <button class="btn btn-primary" onclick="restart()">↻ Try Again</button>
                <a class="btn" href="learn.php">📚 Review OSI</a>
            </div>
        </div>
    `;
}

function restart() {
    loadQuiz();
}

loadQuiz();
</script>

</body>
</html>
