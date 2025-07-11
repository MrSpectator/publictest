<!DOCTYPE html>
<html>
<head>
    <title>Login Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h1 {
            color: #DA612B;
        }
        .success-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        .btn {
            background: #DA612B;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px;
        }
        .btn:hover {
            background: #b94e1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Login Successful!</h1>
        <p>You are seeing this page because you have successfully logged in to iSalesBook.</p>
        <p>This is a placeholder page. The full application dashboard is under development.</p>
        
        <div style="margin-top: 30px;">
            <a href="/swagger" class="btn">View API Documentation</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
