// delcaration

const imgDiv = document.querySelector('#profilePhotoM');
const img = document.querySelector('#photoM');
const file = document.querySelector('#fileM');
const uploadButton = document.querySelector('#uploadButtonM');
const saveButton = document.querySelector('#save');
const phoneNumInput = document.querySelector('#phoneNumber');
const inputBoxes = document.querySelectorAll('input');
const alertMesg = document.querySelector('#alertMesg');

// language selection
// let languages = document.querySelector('#language');
// let options = document.querySelectorAll('#language option');
let languages = document.querySelectorAll('.list input[type="checkbox"]');
let langArray = [];
let userLang = document.querySelector('#userLang');
let langList = Array.apply(null, document.querySelectorAll('.list > *'));



// if user hovers on the profile photo, displays the choose photo button
imgDiv.addEventListener('mouseenter', function(){
    uploadButton.style.display = "block";
});

imgDiv.addEventListener('mouseleave', function(){
    uploadButton.style.display = "none";
});

// change the photo by choosing the different files
file.addEventListener('change', function(){   
    // refers to the file
    const chosenFile = this.files[0];
    if(chosenFile){
        const reader = new FileReader();

        reader.addEventListener('load', function(){
            img.setAttribute('src', reader.result);
        });

        reader.readAsDataURL(chosenFile);
    }
});

// closes languages list when clicking outside .select-field
window.addEventListener("click", (e) => {
    let langMenu = document.querySelector('#language .select-field');
    let langList = langMenu.nextElementSibling
    if (e.target == langMenu || langMenu.contains(e.target)) {
        document.querySelector('#language .list').classList.toggle('show');
        document.querySelector('#language .down-arrow').classList.toggle('rotate180');
    } else if (langList != e.target && !langList.contains(e.target)){
        document.querySelector('#language .list').classList.remove('show');
        document.querySelector('#language .down-arrow').classList.remove('rotate180');
    }
})


for (i = 0; i < languages.length; i++) {
    if(languages[i].checked) {
        langArray.push(languages[i].value);
    }
    languages[i].addEventListener('change', (e) => {
        if (e.target.checked) {
            langArray.push(e.target.value);
        } else if (!e.target.checked) {
            langArray = langArray.filter(lang => lang != e.target.value);
        }
        userLang.value = langArray;
    })
}


// languages.addEventListener('change', (e) => {
//     for (i = 0; i < options.length; i++) {
//         if(options[i].id === e.target.value) {
//             options[i].setAttribute('disabled', 'disabled');
//             langArray.push(e.target.value);
//         }
//     }
//     userLang.value = langArray;
// });


for (i = 0; i < languages.length; i++) {
    if(languages[i].checked) {
        langArray.push(languages[i].value);
    }
    languages[i].addEventListener('change', (e) => {
        if (e.target.checked) {
            langArray.push(e.target.value);
        } else if (!e.target.checked) {
            langArray = langArray.filter(lang => lang != e.target.value);
        }
        userLang.value = langArray;
    })
}


// languages.addEventListener('change', (e) => {
//     for (i = 0; i < options.length; i++) {
//         if(options[i].id === e.target.value) {
//             options[i].setAttribute('disabled', 'disabled');
//             langArray.push(e.target.value);
//         }
//     }
//     userLang.value = langArray;
// });



// frontend checking for the phone number //
// =======================================//

// hide and show message
const display = function(element){
    alertMesg.style.display = "none";
    if(element.classList.contains("red")){
        alertMesg.style.display = "block";
    }
}

// function to check the condition
const countStr = function(phoneNum){
    let count = phoneNum.value.length; // add more condition
    if(count < 11 || count >= 15){
        phoneNum.classList.remove("green");
        phoneNum.classList.add("red"); // display the input box in red color
    } else{
        phoneNum.classList.remove("red");
        phoneNum.classList.add("green");    
    }
    display(phoneNum); // display the alert message
}

// to alert the user while they type their phone number
inputBoxes[3].addEventListener('change', function(){
    countStr(phoneNumInput);
});

// to check the before form submission
// saveButton.addEventListener('click', function(e){
//     countStr(phoneNumInput);
// });

