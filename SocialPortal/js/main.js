const sendDataToServer = (locationToSendData, formdata)=> {
    const formData = new FormData();
    for (const pair of Object.entries(formdata)) {            
    formData.append(pair[0], pair[1]);
    }

    fetch(locationToSendData, {
        method: 'POST',
        body: formData,
    }).then((response) => {
    return response.text();
    }).then((text) => {
    
    dislplayResponse(text.toString());
    }).catch((error) => {
    console.error(error);
    })
}

const dislplayResponse=(response)=>{
    
    const json  = JSON.parse(response);
        if (json.message_key == 'error') {
            
            if (json.error_type == 'empty_value') {
                const data = JSON.parse(json.error_field); 

                for (const field of Object.keys(data)) {
                    
                    const message_field = document.getElementById(data[field]).dataset.message_field;
                    document.querySelector(message_field).classList.add('display');
                    document.querySelector(message_field).innerText = json.message_body;                    
                }
            }else{                
                const message_field = document.getElementById(json.error_field).dataset.message_field;
                document.querySelector(message_field).innerText = json.message_body;
            }

        }
        if (json.message_key == 'success') {
            const userInfo = {
                user_name: document.querySelector('#user_name').value,
            }
            sessionStorage.setItem('recentRegisterdUser', JSON.stringify(userInfo));
            document.location = 'login.html?message=success';
        }
        if (json.message_key == 'login_success') {
            if (user_id) {
            document.location =  json.redirect;
            }
        }
}

const showRecentRegisterdUser = ()=>{
    let urlParameters = new URLSearchParams(location.search);
    
    if (urlParameters.get('message') == 'success') {
        const sessionData = JSON.parse(sessionStorage.getItem('recentRegisterdUser'));        
        if (sessionData) {
            document.getElementById('user_id').classList.add('focus');
            document.getElementById('user_id').value = sessionData.user_name; 
        }
    }
};

const registerForm = document.querySelector('#form');
if (window.location.pathname.split('/').slice(-1) === 'signup.html'){
    registerForm.addEventListener('submit', (event)=>{
        event.preventDefault();
        const obj = {
            user_name: document.querySelector('#user_name').value, 
            password: document.querySelector('#password').value,
            email: document.querySelector('#email').value,
            register: 'register'
        }
        sendDataToServer('signup.php', obj);
    });
}

if (window.location.pathname.split('/').slice(-1) === 'login.html'){
    const btn = document.getElementById('show-hide-button');
    btn.addEventListener('click', (event) =>{
        event.preventDefault();
        const passwordField = document.querySelector(btn.dataset.for.toString());

        if (passwordField && passwordField instanceof HTMLInputElement) {
            if (passwordField.type === 'password') {
                passwordField.setAttribute('type', 'text');
            } else {
                passwordField.setAttribute('type', 'password');
            }
        }
    });
}
if (window.location.pathname.split('/').slice(-1) === 'signup.html'){
    const btn = document.getElementById('show-hide-button');
    btn.addEventListener('click', (event) =>{
        event.preventDefault();
        const passwordField = document.querySelector(btn.dataset.for.toString());

        if (passwordField && passwordField instanceof HTMLInputElement) {
            if (passwordField.type === 'password') {
                passwordField.setAttribute('type', 'text');
            } else {
                passwordField.setAttribute('type', 'password');
            }
        }
    });
}


document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.split('/').slice(-1) == 'login.html') {
        showRecentRegisterdUser();
    }
});

const loginForm = document.querySelector('#login_form');
if (window.location.pathname.split('/').slice(-1) == 'login.html'){
    loginForm.addEventListener('submit', (event) => {
        // event.preventDefault();

        const object = {
            user_id: document.querySelector('#user_id').value,
            user_password: document.querySelector('#user_password').value,
            login: 'login'
        };
        sendDataToServer('user-login.php', object);
    });
}


