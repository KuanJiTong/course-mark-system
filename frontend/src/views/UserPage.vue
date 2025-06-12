<template>
  <h2 class="mb-4">User Management</h2>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <button @click="openAddModal" class="mb-2 btn btn-primary">
        Add User
      </button>
      <input
        v-model="searchQuery"
        @input = "searchUser"
        type="text"
        class="form-control"
        placeholder="Search users..."
      />
    </div>
  </div>

  <div class="table-responsive shadow-sm rounded">
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th class="p-2 text-left">#</th>
        <th class="p-2 text-left">Login ID</th>
        <th class="p-2 text-left">Name</th>
        <th class="p-2 text-left">Email</th>
        <th class="p-2 text-left">Faculty</th>
        <th class="p-2 text-left">Role</th>
        <th class="p-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="users.length === 0" class="text-center text-muted">
        <td colspan="7">No users added yet.</td>
      </tr>
      <tr v-for="(user, index) in users" :key="user.userId" class="text-center text-muted">
        <td class="p-2">{{ index + 1 }}</td>
        <td class="p-2">{{ user.loginId }}</td>
        <td class="p-2">{{ user.name }}</td>
        <td class="p-2">{{ user.email }}</td>
        <td class="p-2">{{ user.facultyAbbreviation }}</td>
        <td class="p-2">
          <ul style="list-style: none; padding-left: 0; margin: 0;">
            <li v-for="(role, index) in user.roleNames" :key="index">
              {{ role }}
            </li>
          </ul>
        </td>
        <td class="p-2 text-center">
          <div class="icon-row">
            <i class="bi bi-pencil-square text-primary mx-2" data-bs-toggle="tooltip" title="Edit" @click="openEditModal(user)"></i>
            <i class="bi bi-lock-fill mx-2" data-bs-toggle="tooltip" title="Reset Password"></i>
            <i class="bi bi-trash-fill text-danger mx-2" data-bs-toggle="tooltip" title="Delete"></i>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  </div>

  <UserModal
    :show="showModal"
    :userData="selectedUser"
    @close="closeModal"
    @submit-user="handleSubmit"
  />
</template>

<script>
import UserModal from '../components/UserModal.vue'; // Adjust path based on your structure

export default {
  components: { UserModal },
  data() {
    return {
      showModal: false,
      selectedUser: {},
      users: [],
      searchQuery: ''
    };
  },
  async created(){
    await this.fetchAllUsers();
  },
  methods: {
    async fetchAllUsers(){
      try {
        const url = `http://localhost:3000/users`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch users');

        const data = await response.json();
        this.users = data; 
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    openAddModal() {
      this.selectedUser = {};
      this.showModal = true;
    },
    openEditModal(user) {
      this.selectedUser = { ...user };
      this.showModal = true;
      console.log(user);
    },
    closeModal() {
      this.showModal = false;
    },
    async handleSubmit(user) {
      if (user.userId) {
        this.updateUser(user);
      } else {
        await this.addUser(user);
      }
      this.closeModal();
    },
    async addUser(newUser) {
      console.log(newUser);
      // Send POST request
      await fetch('http://localhost:3000/user', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(newUser)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add user');
        }
        return await response.json();
      })
      .then(data => {
        alert('User added:', data);

        // Fetch the updated course list
        this.fetchAllUsers();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },
    async updateUser(user){
      try {
        const response = await fetch(`http://localhost:3000/user/${user.userId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(user),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to update user.');
        } else {
          alert('User updated successfully.');
          this.fetchAllUsers();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async searchUser() {
      try {
        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllUsers();
          return;
        }

        const url = `http://localhost:3000/users?keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch users');

        const data = await response.json();
        this.users = data;
      } catch (error) {
        console.error('Error searching users:', error);
      }
    },
  }
};
</script>

<style scoped>
.icon-row {
  display: flex;
  justify-content: center;
  gap: 3px; 
}

.bi{
  cursor: pointer;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.bi:hover {
  background-color: #b5b3b368; 
}

.bi:active {
  transform: scale(0.9); 
  background-color: #e0e0e0; 
}
</style>
