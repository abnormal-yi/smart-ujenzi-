import React from 'react';
import { useState, useEffect } from 'react';
import { LucidePlus, LucideSearch } from 'lucide-react';

export default function Tasks() {
  const [tasks, setTasks] = useState<any[]>([]);
  const [projects, setProjects] = useState<any[]>([]);
  const [users, setUsers] = useState<any[]>([]);
  const [showModal, setShowModal] = useState(false);
  const [formData, setFormData] = useState({ name: '', description: '', project_id: '', supervisor_id: '', deadline: '' });

  const fetchData = async () => {
    const token = localStorage.getItem('token');
    const headers = { 'Authorization': `Bearer ${token}` };
    
    const [tasksRes, projRes, userRes] = await Promise.all([
      fetch('/api/tasks', { headers }),
      fetch('/api/projects', { headers }),
      fetch('/api/users', { headers })
    ]);
    
    if (tasksRes.ok) setTasks(await tasksRes.json());
    if (projRes.ok) setProjects(await projRes.json());
    if (userRes.ok) setUsers(await userRes.json());
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    const res = await fetch('/api/tasks', {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify(formData)
    });
    
    if (res.ok) {
      setShowModal(false);
      fetchData();
      setFormData({ name: '', description: '', project_id: '', supervisor_id: '', deadline: '' });
    }
  };

  const updateStatus = async (id: number, status: string) => {
    const token = localStorage.getItem('token');
    await fetch(`/api/tasks/${id}/status`, {
      method: 'PUT',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({ status })
    });
    fetchData();
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Tasks</h2>
        <button 
          onClick={() => setShowModal(true)}
          className="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
        >
          <LucidePlus className="w-4 h-4 mr-2" />
          Assign Task
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
              <th className="p-4 font-medium">Task</th>
              <th className="p-4 font-medium">Project</th>
              <th className="p-4 font-medium">Supervisor</th>
              <th className="p-4 font-medium">Deadline</th>
              <th className="p-4 font-medium">Status & Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {tasks.map((task) => (
              <tr key={task.id} className="hover:bg-gray-50">
                <td className="p-4 font-medium text-gray-900">{task.name}</td>
                <td className="p-4 text-gray-600">{task.project_name || 'N/A'}</td>
                <td className="p-4 text-gray-600">{task.supervisor_name || 'Unassigned'}</td>
                <td className="p-4 text-gray-600">{task.deadline || 'N/A'}</td>
                <td className="p-4 flex items-center space-x-2">
                  <span className={`px-2.5 py-1 rounded-full text-xs font-medium ${
                    task.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                    task.status === 'Completed' ? 'bg-green-100 text-green-800' :
                    'bg-slate-100 text-slate-800'
                  }`}>
                    {task.status}
                  </span>
                  <select 
                    value={task.status}
                    onChange={(e) => updateStatus(task.id, e.target.value)}
                    className="text-xs border rounded px-1 py-0.5 outline-none bg-white"
                  >
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                  </select>
                </td>
              </tr>
            ))}
            {tasks.length === 0 && (
              <tr>
                <td colSpan={5} className="p-8 text-center text-gray-500">No tasks found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">Create New Task</h3>
            </div>
            <form onSubmit={handleSubmit} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Task Name</label>
                <input required type="text" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select required value={formData.project_id} onChange={e => setFormData({...formData, project_id: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500">
                  <option value="">Select Project</option>
                  {projects.map(p => (
                    <option key={p.id} value={p.id}>{p.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Supervisor</label>
                <select value={formData.supervisor_id} onChange={e => setFormData({...formData, supervisor_id: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500">
                  <option value="">Select Supervisor</option>
                  {users.map(u => (
                    <option key={u.id} value={u.id}>{u.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                <input type="date" value={formData.deadline} onChange={e => setFormData({...formData, deadline: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">Create Task</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
