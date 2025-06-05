<template>
  <div class="login-container">
    <div class="login-box">
      <div class="login-content">
        <div class="logo-box">
          <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
        </div>
        <h2>Login</h2>
        <div>
          <label for="userId" class="form-label"><b>Username</b></label>
          <div class="form-input">
            <i class="bi bi-person bi-solid"></i>
            <input id="userId" type="text" class="form-control" v-model="username" placeholder="Enter Username"/>
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label"><b>Password</b></label>
          <div class="form-input">
            <i class="bi bi-key bi-solid"></i><i :class="`bi ${passIcon} bi-hide`" @click="pass"></i>
            <input id="password" :type="inputType" class="form-control" v-model="password" placeholder="Enter Password"/>
          </div>
        </div>
        
        <button type="submit">Login</button>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      userId: '',
      password: '',
      message: null,
      inputType: 'password',
      passIcon: 'bi-eye-slash',
      hide: true
    };
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
  width: 500px;
  height: 500px;
}

.login-content {
  width: 100%;
  padding: 1.5rem;
  text-align: left;
}

.logo-box{
  position: relative;
  width: 100%;
  height: 150px;
  text-align: center;
}

.logo {
  position: absolute;
  bottom:-180px;
  right: -5px;
  width: 100%;
  height: 350%;
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

<!-- <form>
  <div v-if="message" class="alert alert-danger" role="alert">
    {{ message }}
  </div>
  <div class="mb-3">
    <label for="userId" class="form-label"><b>User ID</b></label>
    <div class="form-input">
      <i class="bi bi-person bi-solid"></i>
      <input id="userId" type="text" class="form-control" v-model="userId" placeholder="Enter Matric No / Staff ID"/>
    </div>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label"><b>Password</b></label>
    <div class="form-input">
      <i class="bi bi-key bi-solid"></i><i :class="`bi ${passIcon} bi-hide`" @click="pass"></i>
      <input id="password" :type="inputType" class="form-control" v-model="password" placeholder="Enter Password"/>
    </div>
  </div>

  <button type="submit" class="btn btn-red text-white w-100"><b>Login</b></button>
</form> -->