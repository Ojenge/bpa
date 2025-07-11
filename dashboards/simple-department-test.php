<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Department Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        #console { background: #f0f0f0; padding: 10px; height: 200px; overflow-y: auto; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Simple Department Loading Test</h1>
    
    <div class="test-section">
        <h3>Department Select</h3>
        <select id="departmentSelect">
            <option value="">Loading...</option>
        </select>
        <button onclick="loadDepartments()">Load Departments</button>
        <button onclick="clearConsole()">Clear Console</button>
    </div>
    
    <div class="test-section">
        <h3>Console Output</h3>
        <div id="console"></div>
    </div>

    <script>
        function log(message, type = 'info') {
            const console = document.getElementById('console');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'blue';
            console.innerHTML += `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
        }

        function clearConsole() {
            document.getElementById('console').innerHTML = '';
        }

        function loadDepartments() {
            log('Starting department load...', 'info');
            
            const select = document.getElementById('departmentSelect');
            if (!select) {
                log('ERROR: Department select element not found!', 'error');
                return;
            }
            
            log('Department select element found', 'success');
            
            fetch('/bpa/dashboards/get-department-list.php')
                .then(response => {
                    log(`Response status: ${response.status}`, 'info');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    log('JSON data received:', 'success');
                    log(`Found ${data.departments?.length || 0} departments`, 'info');
                    
                    // Clear the select
                    select.innerHTML = '';
                    
                    if (data.departments && data.departments.length > 0) {
                        data.departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.id;
                            option.textContent = dept.name;
                            select.appendChild(option);
                            log(`Added: ${dept.name} (${dept.id})`, 'success');
                        });
                        log('All departments loaded successfully!', 'success');
                    } else {
                        log('No departments found in response', 'error');
                        select.innerHTML = '<option value="">No departments found</option>';
                    }
                })
                .catch(error => {
                    log(`ERROR: ${error.message}`, 'error');
                    select.innerHTML = '<option value="">Error loading departments</option>';
                });
        }

        // Auto-load on page load
        document.addEventListener('DOMContentLoaded', function() {
            log('Page loaded, auto-loading departments...', 'info');
            setTimeout(loadDepartments, 1000);
        });
    </script>
</body>
</html> 