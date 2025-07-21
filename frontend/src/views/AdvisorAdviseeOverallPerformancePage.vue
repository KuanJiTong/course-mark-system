<template>
  <div>
    <h2 class="mt-4 mb-4">Advisee's Overall Course Performance</h2>
    <div class="form-group">
      <label for="advisee">Advisee:</label>
      <select v-model="selectedAdviseeId" @change="fetchAllData" required class="form-select">
        <option disabled value="">-- Select Advisee --</option>
        <option v-for="advisee in advisees" :key="advisee.student_id" :value="advisee.student_id">
          {{ advisee.student_name }} ({{ advisee.matric_no }})
        </option>
      </select>
    </div>
    <button v-if="selectedAdviseeId && enrollments.length" class="btn btn-primary mb-3" @click="downloadCSV">Download CSV Report</button>
    <div v-if="enrollments.length">
      <div v-for="enrollment in enrollments" :key="enrollment.section_id" class="course-section-summary">
        <h2>{{ enrollment.course_code }}-{{ enrollment.section_number }} {{ enrollment.course_name }}</h2>
        <table>
          <thead>
            <tr>
              <th>Component</th>
              <th>Mark</th>
              <th>Max Mark</th>
              <th>Class Average</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comp in getComponentMarks(enrollment.section_id)" :key="comp.component_id">
              <td>{{ comp.component_name }}</td>
              <td>{{ comp.mark }}</td>
              <td>{{ comp.max_mark }}</td>
              <td>{{ getClassAverage(enrollment.section_id, comp.component_id) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="summary-row">
          <span><strong>Final Exam Mark:</strong> {{ getFinalExamMark(enrollment.section_id) }}</span>
          <span><strong>Total Mark:</strong> {{ getTotalMark(enrollment.section_id) }}</span>
          <span v-if="getRankInfo(enrollment.section_id)"><strong>Rank:</strong> {{ getRankInfo(enrollment.section_id).rank }} / {{ getRankInfo(enrollment.section_id).totalStudents }}</span>
          <span v-if="getRankInfo(enrollment.section_id)"><strong>Percentile:</strong> {{ getRankInfo(enrollment.section_id).percentile }}%</span>
        </div>
      </div>
    </div>
    <div v-if="selectedAdviseeId && !enrollments.length && loaded">No enrollments found for this advisee.</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      advisorId: null, 
      advisees: [],
      selectedAdviseeId: '',
      enrollments: [],
      marks: {}, 
      classAverages: {}, 
      finalExamMarks: {}, 
      totalMarks: {},
      rankInfo: {}, 
      loaded: false,
      errorMessage: ''
    };
  },
  methods: {
    // Get authenticated user data
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.advisorId = user.user_id; 
        return true;
      }
      return false;
    },
    async fetchAdvisees() {
      try {
        const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.advisorId}`);
        if (!res.ok) throw new Error('Failed to fetch advisees');
        this.advisees = await res.json();
      } catch (error) {
        console.error('Error fetching advisees:', error);
        this.errorMessage = 'Failed to load advisees.';
      }
    },
    async fetchAllData() {
      this.enrollments = [];
      this.marks = {};
      this.classAverages = {};
      this.finalExamMarks = {};
      this.totalMarks = {};
      this.rankInfo = {};
      this.loaded = false;
      this.errorMessage = '';
      if (!this.selectedAdviseeId) return;
      
      try {
        // Fetch enrollments
        const enrollRes = await fetch(`http://localhost:3000/student/enrollments?student_id=${this.selectedAdviseeId}`);
        if (!enrollRes.ok) throw new Error('Failed to fetch enrollments');
        this.enrollments = await enrollRes.json();
        
        // For each enrollment, fetch marks, class averages, and rank
        await Promise.all(this.enrollments.map(async (enrollment) => {
          try {
            // Marks
            const marksRes = await fetch(`http://localhost:3000/student/marks?student_id=${this.selectedAdviseeId}&course_id=${enrollment.course_id}&section_id=${enrollment.section_id}`);
            if (marksRes.ok) {
              const marksData = await marksRes.json();
              // Map camelCase keys to snake_case for marks
              this.marks[enrollment.section_id] = Array.isArray(marksData.marks)
                ? marksData.marks.map(mark => ({
                    mark_id: mark.markId,
                    component_id: mark.componentId,
                    mark: mark.mark,
                    component_name: mark.componentName,
                    max_mark: mark.maxMark
                  }))
                : [];
              // Handle both camelCase and snake_case for final exam and total mark
              this.finalExamMarks[enrollment.section_id] =
                marksData.final_exam_mark ?? marksData.finalExam?.mark ?? '-';
              this.totalMarks[enrollment.section_id] =
                marksData.total_mark ?? marksData.totalMark ?? '-';
            }
            
            // Class averages
            const avgRes = await fetch(`http://localhost:3000/advisor/component-averages?section_id=${enrollment.section_id}`);
            if (avgRes.ok) {
              this.classAverages[enrollment.section_id] = await avgRes.json();
            }
            
            // Rank
            const rankRes = await fetch(`http://localhost:3000/student/rank?student_id=${this.selectedAdviseeId}&course_id=${enrollment.course_id}&section_id=${enrollment.section_id}`);
            if (rankRes.ok) {
              this.rankInfo[enrollment.section_id] = await rankRes.json();
            }
          } catch (error) {
            console.error(`Error fetching data for section ${enrollment.section_id}:`, error);
          }
        }));
        
        this.loaded = true;
      } catch (error) {
        console.error('Error fetching overall performance data:', error);
        this.errorMessage = 'Failed to load performance data.';
        this.loaded = true;
      }
    },
    getComponentMarks(section_id) {
      const arr = Array.isArray(this.marks[section_id]) ? this.marks[section_id] : [];
      return arr.filter(m => m.component_name !== 'Final Exam');
    },
    getFinalExamMark(section_id) {
      return this.finalExamMarks[section_id] ?? '-';
    },
    getTotalMark(section_id) {
      return this.totalMarks[section_id] ?? '-';
    },
    getClassAverage(section_id, component_id) {
      const arr = this.classAverages[section_id] || [];
      const found = arr.find(a => a.component_id == component_id);
      return found && found.average_mark ? Number(found.average_mark).toFixed(2) : '-';
    },
    getRankInfo(section_id) {
      return this.rankInfo[section_id] || null;
      },
    downloadCSV() {
      if (!this.selectedAdviseeId || !this.enrollments.length) return;
      let csv = 'Course,Section,Component,Mark,Max Mark,Class Average,Final Exam,Total,Rank,Percentile\n';
      this.enrollments.forEach(enrollment => {
        const sectionMarks = this.marks[enrollment.section_id] || [];
        const classAverages = this.classAverages[enrollment.section_id] || [];
        const finalExam = this.getFinalExamMark(enrollment.section_id);
        const total = this.getTotalMark(enrollment.section_id);
        const rankInfo = this.getRankInfo(enrollment.section_id) || {};
        sectionMarks.forEach(mark => {
          const avgObj = classAverages.find(a => a.component_id == mark.component_id);
          const avg = avgObj && avgObj.average_mark ? Number(avgObj.average_mark).toFixed(2) : '-';
          csv += `"${enrollment.course_name}","${enrollment.section_number}","${mark.component_name}","${mark.mark}","${mark.max_mark}","${avg}","${finalExam}","${total}","${rankInfo.rank || ''}","${rankInfo.percentile || ''}"\n`;
        });
      });
      // Download CSV
      const advisee = this.advisees.find(a => a.student_id == this.selectedAdviseeId);
      const filename = advisee ? `${advisee.student_name}_overall_performance.csv` : 'advisee_overall_performance.csv';
      const blob = new Blob([csv], { type: 'text/csv' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  },
  async mounted() {
    if (this.getAuthenticatedUser()) {
      await this.fetchAdvisees();
    } else {
      this.errorMessage = 'Authentication required. Please login.';
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 20px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  font-weight: bold;
}
select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
.course-section-summary {
  margin-bottom: 2.5rem;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 1.5rem;
  background: #fafbfc;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 0.5rem;
}
th, td {
  border: 1px solid #ddd;
  padding: 0.5rem 1rem;
  text-align: left;
}
th {
  background: #f5f5f5;
}
.summary-row {
  margin-top: 1rem;
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}
.error-message {
  color: red;
  margin-top: 10px;
}
</style> 