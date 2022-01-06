const VVERNER = {
   submitForm: (formData) => {
      return new Promise((resolve, reject) => {
         fetch(app_data.url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            resolve(data);
         })
         .catch(error => {
            reject(error)
         });
      }) 
   }
}