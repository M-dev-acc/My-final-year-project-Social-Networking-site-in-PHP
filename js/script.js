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

const showPassword = (showPasswordBtn) => {

    passwordField = document.querySelector(showPasswordBtn.dataset.for.toString())
    if (passwordField && passwordField instanceof HTMLInputElement) {
        if (passwordField.type === 'password') {
            passwordField.setAttribute('type', 'text');
        } else {
            passwordField.setAttribute('type', 'password');
        }
    }
        
}