<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        td { padding: 4px 6px; vertical-align: top; }
        .label { color: #64748b; width: 25%; }
        .q { font-weight: bold; margin-top: 14px; }
        .a { border: 1px solid #cbd5e1; background: #f8fafc; padding: 8px; margin-top: 4px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>{{ $assessment->cycle->title }}</h1>
    <div>Submitted at: {{ $assessment->submitted_at?->format('Y-m-d H:i') }}</div>
    <table>
        <tr><td class="label">Name</td><td>{{ $assessment->user->name }}</td><td class="label">Employee ID</td><td>{{ $assessment->user->employee_id }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $assessment->user->email }}</td><td class="label">Position</td><td>{{ $assessment->user->position }}</td></tr>
        <tr><td class="label">Department</td><td colspan="3">{{ $assessment->user->department }}</td></tr>
    </table>
    @foreach ($answers as $answer)
        <div class="q">{{ $loop->iteration }}. {{ $answer->question->question_en }}</div>
        <div class="a">{{ $answer->answer_text }}</div>
    @endforeach
</body>
</html>
