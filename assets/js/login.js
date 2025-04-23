function login() {
  let email = document.getElementById("email").value;
  let pass = document.getElementById("password").value;
  url=config.urlBase+"auth.php";
  
  console.log(url);

  axios.post(url, { email: email, password: pass }, { withCredentials: true })
      .then(response => {
          if (response.data.status === "success") {
              window.location.href = "../app.html"; 
          } else {
              alert(response.data.message);
          }
      })
      .catch(error => {
          console.error("Error:", error);
          alert("Hubo un problema con el servidor.");
      });
}