<template>
  <div class="login-container">
    <div class="login-box">
      <div class="login-content">
        <div class="logo-box">
          <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
        </div>
        <h2>Login</h2>
        <div>
          <label for="userId" class="form-label"><b>Login ID</b></label>
          <div class="form-input">
            <i class="bi bi-person bi-solid"></i>
            <input id="userId" type="text" class="form-control" v-model="loginID" placeholder="Enter User ID"/>
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label"><b>Password</b></label>
          <div class="form-input">
            <i class="bi bi-key bi-solid"></i><i :class="`bi ${passIcon} bi-hide`" @click="pass"></i>
            <input id="password" :type="inputType" class="form-control" v-model="password" placeholder="Enter Password"/>
          </div>
        </div>
        
        <!-- Add error/success message display -->
        <div v-if="message" class="alert" :class="message.includes('error') ? 'alert-danger' : 'alert-success'" style="margin-bottom: 1rem;">
          {{ message }}
        </div>
        
        <button type="submit" @click="login">Login</button>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      loginID: '',
      password: '',
      message: null,
      inputType: 'password',
      passIcon: 'bi-eye-slash',
      hide: true
    };
  },
  mounted() {
    // Check if user was redirected from logout
    if (this.$route.query.logout) {
      this.message = 'You have been successfully logged out.';
    }
  },
  methods: {
    pass(){
      if(this.hide){
        this.inputType = 'text';
        this.hide = false;
        this.passIcon = 'bi-eye';
      }
      else{
        this.inputType = 'password';
        this.hide = true;
        this.passIcon = 'bi-eye-slash';
      }
    },
    async login() {
      console.log('Login button clicked');
      this.message = null;
      try {
        console.log('Sending login request...');
        const res = await fetch('http://localhost:3000/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ loginID: this.loginID, password: this.password })
        });
        console.log('Response received:', res.status);
        const data = await res.json();
        console.log('Response data:', data);
        if (res.ok && data.token) {
          sessionStorage.setItem('jwt', data.token);
          sessionStorage.setItem('user', JSON.stringify(data.user));
          this.$router.push('/');
        } else {
          this.message = data.error || 'Login failed';
        }
      } catch (e) {
        console.error('Login error:', e);
        this.message = 'Network error: ' + e.message;
      }
    }
  }
}
</script>
  
<style scoped>
h2{
  text-align: center;
  font-weight: bold;
}
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: hsla(0, 0%, 95%, 0.813);
}

.login-box {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: white;
  padding: 1rem;
  border-radius: 20px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  width: 550px;
  height: 550px;
}

.login-content {
  width: 100%;
  padding: 1.5rem;
  text-align: left;
}

.logo-box{
  width: 100%;
  height: 150px;
  display:flex;
  align-items: center;
  justify-content: center;
}

.logo{
  width: 65%;
}

input {
  display: block;
  height: 40px;
  width: 100%;
  padding: 0px 35px 3px 35px;
  margin-bottom: 0.4rem;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 1rem;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}

.bi-solid{
  position: absolute;
  top: 4px;
  left: 10px;
  font-size: 20px;
}

.bi-hide{
  position: absolute;
  top: 4px;
  right: 10px;
  font-size: 20px;
}

.form-input{
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  justify-content: center;
}
</style>