<template>
    <button class="btn btn-secondary mb-4" @click="goBack">Back</button>
    <h2 class="mb-4" v-if="course">Section Page - {{ course.courseCode }} {{ course.courseName }}</h2>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <div>
        <button
          class="mb-2"
          :class="showForm ? 'btn btn-danger' : 'btn btn-success'"
          @click="showForm ? reset() : toggleForm()"
        >
          {{ showForm ? 'Cancel' : 'Add Section' }}
        </button>

        <div v-if="showForm" class="d-flex align-items-center gap-2 mb-3">
          <input type="text" v-model="newSection.sectionNumber" placeholder="Section No." class="form-control" style="width: 150px;">
          <input type="text" v-model="newSection.capacity" placeholder="Capacity" class="form-control" style="width: 250px;">
          <button class="btn btn-primary" @click="addSection">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th style="width: 120px;">Section No.</th>
          <th style="width: 50px;">Capacity</th>
          <th style="width: 120px;"># of students</th>
          <th style="width: 550px;">Lecturer</th>
          <th style="width: 200px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="sections.length === 0" class="text-center text-muted">
          <td colspan="8">No section added yet.</td>
        </tr>
        <tr v-for="(section, index) in sections" :key="index">
          <td>{{ index + 1 }}</td>
          <td>
            <span v-if="editingSectionId !== section.sectionId">{{ section.sectionNumber }}</span>
            <input v-else style="width: 150px;" v-model="section.sectionNumber" type="text" />
          </td>
          <td>
            <span v-if="editingSectionId !== section.sectionId">{{ section.capacity }}</span>
            <input v-else style="width: 100%;" v-model="section.capacity" type="text" />
          </td>
          <td>
            <span>{{ section.studentCount }}</span>
          </td>
          <td>
            <span v-if="assignLecturerSectionId !== section.sectionId">{{ section.lecturerName }}</span>
            <div v-else class="wrapper">
              <div @click="toggleMenu" class="select-btn">
                <span><strong>{{ section.lecturerName? section.lecturerName : 'Select Lecturer'}}</strong></span>
                <i class="bi bi-chevron-down"></i>
              </div>
              <div v-show="selectMenu.showMenu" class="content">
                <div class="search">
                  <input type="text" placeholder="Search" v-model="searchQuery" @input="searchLecturer">
                </div>
                <ul class="options">
                  <li v-for="lecturer in lecturers" :key="lecturer.lecturerId" @click="selectLecturer(section,lecturer)">
                    <strong>{{ lecturer.lecturerName }}</strong>
                    <small> {{ lecturer.email }}</small>
                  </li>
                </ul>
              </div>
            </div>
          </td>

          <td>
            <div class="icon-row">
              <!-- Default view -->
              <template v-if="editingSectionId !== section.sectionId && assignLecturerSectionId !== section.sectionId">
                <i
                  class="bi bi-person-plus-fill mx-2"
                  data-bs-toggle="tooltip"
                  title="Assign Lecturer"
                  @click="startAssignLecturer(section)"
                ></i>
                <i
                  class="bi bi-pencil-square text-primary mx-2"
                  data-bs-toggle="tooltip"
                  title="Edit"
                  @click="startEdit(section)"
                ></i>
                <i
                  class="bi bi-trash-fill text-danger mx-2"
                  data-bs-toggle="tooltip"
                  title="Delete"
                  @click="deleteSection(section.sectionId)"
                ></i>
              </template>

              <!-- Edit mode -->
              <template v-else-if="editingSectionId === section.sectionId">
                <button class="btn btn-sm btn-primary mx-1" @click="updateSection(section)">Submit</button>
                <button class="btn btn-sm btn-danger mx-1" @click="cancelEdit(section)">Cancel</button>
              </template>

              <!-- Assign lecturer mode -->
              <template v-else-if="assignLecturerSectionId === section.sectionId">
                <button class="btn btn-sm btn-success mx-1" @click="assignLecturer(section)">Assign</button>
                <button class="btn btn-sm btn-secondary mx-1" @click="cancelAssignLecturer(section)">Cancel</button>
              </template>
            </div>

          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "SectionPage",
  data() {
    return {
      courseId: null,
      course: null,
      editingSectionId: null,
      assignLecturerSectionId: null,
      newSection: {
        sectionNumber: '',
        capacity: null,
        courseId: null
      },
      sections: [],
      lecturers: [],
      showForm: false,
      selectMenu: {
        showMenu: false
      },
      searchQuery: '',
      lecturer: null
    };
  },
  async created(){
      this.courseId = this.$route.params.courseId;
      await this.fetchCourse();
      await this.fetchAllSections();
      await this.fetchAllLecturers();
  },
  methods: {
    async fetchAllSections(){
      try {
        const courseId = this.courseId; 
        const url = `http://localhost:3000/section?course_id=${courseId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch sections');

        const data = await response.json();
        this.sections = data; 
      } catch (error) {
        console.error('Error fetching sections:', error);
      }
    },
    async fetchCourse(){
      try {
        const courseId = this.courseId; 
        const url = `http://localhost:3000/course/${courseId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch sections');

        const data = await response.json();
        this.course = data; 
      } catch (error) {
        console.error('Error fetching sections:', error);
      }
    },
    async fetchAllLecturers(){
      try {
        const facultyId = this.course.facultyId; 
        const url = `http://localhost:3000/lecturers/${facultyId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch lecturers');

        const data = await response.json();
        this.lecturers = data; 
      } catch (error) {
        console.error('Error fetching lecturers:', error);
      }
    },
    goBack() {
      this.$router.back();
    },
    reset(){
      this.newSection.sectionNumber = '';
      this.newSection.capacity = null;
      this.showForm = false;
    },
    toggleForm(){
      this.showForm = true;
    },
    toggleMenu(){
      this.selectMenu.showMenu = !this.selectMenu.showMenu;
    },
    selectLecturer(section,lecturer){
      section.lecturerId = lecturer.lecturerId;
      section.lecturerName = lecturer.lecturerName;
      this.toggleMenu();
    },
    async searchLecturer() {
      try {
        const facultyId = this.course.facultyId; 

        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllLecturers();
          return;
        }

        const url = `http://localhost:3000/lecturers/${facultyId}?keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch lecturers');

        const data = await response.json();
        this.lecturers = data;
      } catch (error) {
        console.error('Error searching lecturers:', error);
      }
    },
    startEdit(section) {
      if (this.editingSectionId) {
        const prevSection = this.sections.find(c => c.sectionId === this.editingSectionId);
        if (prevSection && prevSection._backup) {
          Object.assign(prevSection, prevSection._backup);
          delete prevSection._backup;
        }
      }
      this.editingSectionId = section.sectionId;
      section._backup = { ...section };
    },
    cancelEdit(section) {
      if (section._backup) {
        Object.assign(section, section._backup);
        delete section._backup;
      }
      this.editingSectionId = null;
    },
    startAssignLecturer(section){
      if (this.assignLecturerSectionId) {
        const prevSection = this.sections.find(c => c.sectionId === this.assignLecturerSectionId);
        if (prevSection && prevSection._backup) {
          Object.assign(prevSection, prevSection._backup);
          delete prevSection._backup;
        }
      }
      section._backup = { ...section };
      this.assignLecturerSectionId = section.sectionId;
    },
    cancelAssignLecturer(section){
      if (section._backup) {
        Object.assign(section, section._backup);
        delete section._backup;
      }
      this.assignLecturerSectionId = null;
    },
    async addSection() {
      // Prepare the data to be sent
      this.newSection.courseId = this.courseId;
      const newSection = this.newSection;

      // Send POST request
      await fetch('http://localhost:3000/section', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(newSection)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add section');
        }
        return await response.json();
      })
      .then(data => {
        alert('Section added:', data);

        this.reset();

        // Fetch the updated section list
        this.fetchAllSections();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },    
    async updateSection(section){
      this.editingSectionId = null;
      delete section._backup;
      try {
        const response = await fetch(`http://localhost:3000/section/${section.sectionId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(section),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to update section.');
        } else {
          alert('Section updated successfully.');
          this.fetchAllSections();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async assignLecturer(section){
      this.assignLecturerSectionId = null;
      delete section._backup;

      const lecturer = {
        lecturerId: section.lecturerId
      }
      try {
        const response = await fetch(`http://localhost:3000/section/${section.sectionId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(lecturer),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert(`Failed to assign lecturer to Section ${section.sectionNumber}.`);
        } else {
          alert(`Lecturer assigned successfully to Section ${section.sectionNumber}.`);
          this.fetchAllSections();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async deleteSection(sectionId) {
      if (!confirm('Are you sure you want to delete this section?')) return;

      try {
        const response = await fetch(`http://localhost:3000/section/${sectionId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
        });

        const result = await response.json();

        if (response.ok) {
          alert(result.message || 'Section deleted successfully.');
          this.fetchAllSections();
        } else {
          alert(result.error || 'Failed to delete section.');
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting.');
      }
    }
  },
};
</script>

<style scoped>
.table-responsive {
  background-color: #fff;
}

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

.wrapper{
  width: 550px;
  --wrapper-width: 550px;
}

.options li,.select-btn{
  display: flex;
  cursor: pointer;
  align-items: center;
}

.select-btn{
  height: 30px;
  font-size: 0.9rem;
  padding: .275rem 2.25rem .275rem .75rem;
  justify-content: space-between;
  border-radius: 7px;
  background: #fff;
  position: relative;
  border: 0.2px solid #b3b3b3;
}

.select-btn i{
  font-size: 15px;
  position: absolute;
  right: 5px;
  bottom: 1px;
}

.content{
  border: 0.2px solid #b3b3b3;
  width: var(--wrapper-width);
  position: absolute;
  z-index: 999;
  background: #fff;
  margin-top: 5px;
  border-radius: 7px;
  padding: 5px 10px 5px 10px;
}

.content .search{
  position: relative;
}

.search input{
  height: 36px;
  width: 100%;
  font-size: 1rem;
  outline: none;
  border-radius: 5px;
  padding: .375rem .75rem;
  border: 1px solid #b3b3b3;
}

.content .options{
  margin-top: 10px;
  max-height: 250px;
  overflow-y: auto;
  padding-left: 0px !important;
  margin-bottom: 0px !important;
}

.options li{
  height: 36px;
  padding: .375rem .75rem;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  font-size: 15px;
  line-height: 0.9;
  border-radius: 5px;
}

.options li:hover{
  background: #f2f2f2;
}


</style>
