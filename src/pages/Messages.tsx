import React, { useState, useEffect, useRef } from 'react';
import { LucideMessageSquare, LucideSend, LucideSearch } from 'lucide-react';

export default function Messages() {
  const [projects, setProjects] = useState<any[]>([]);
  const [selectedProject, setSelectedProject] = useState<any | null>(null);
  const [messages, setMessages] = useState<any[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const fetchProjects = async () => {
      const token = localStorage.getItem('token');
      const res = await fetch('/api/projects', { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) {
        const data = await res.json();
        setProjects(data);
        if (data.length > 0) {
          setSelectedProject(data[0]);
        }
      }
    };
    fetchProjects();
  }, []);

  useEffect(() => {
    if (!selectedProject) return;
    const fetchMessages = async () => {
      const token = localStorage.getItem('token');
      const res = await fetch(`/api/projects/${selectedProject.id}/messages`, { headers: { 'Authorization': `Bearer ${token}` } });
      if (res.ok) {
        setMessages(await res.json());
      }
    };
    fetchMessages();
    const interval = setInterval(fetchMessages, 5000);
    return () => clearInterval(interval);
  }, [selectedProject]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const sendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim() || !selectedProject) return;

    const token = localStorage.getItem('token');
    const res = await fetch(`/api/projects/${selectedProject.id}/messages`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: newMessage.trim() })
    });

    if (res.ok) {
      setNewMessage('');
      // Optimistic update can go here, but interval will catch it quickly
    }
  };

  const decodeToken = () => {
    try {
      const token = localStorage.getItem('token');
      if (token) return JSON.parse(atob(token.split('.')[1]));
    } catch { return null; }
    return null;
  };
  const user = decodeToken();

  return (
    <div className="h-[calc(100vh-8rem)] flex shadow-sm rounded-xl border border-gray-200 overflow-hidden bg-white">
      {/* Sidebar - Projects List */}
      <div className="w-1/3 min-w-[250px] border-r border-gray-200 bg-gray-50 flex flex-col">
        <div className="p-4 border-b border-gray-200">
          <h2 className="text-lg font-bold text-gray-800 flex items-center">
            <LucideMessageSquare className="w-5 h-5 mr-2 text-yellow-600" />
            Project Discussions
          </h2>
        </div>
        <div className="flex-1 overflow-y-auto">
          {projects.map((p) => (
            <div 
              key={p.id}
              onClick={() => setSelectedProject(p)}
              className={`p-4 border-b border-gray-100 cursor-pointer transition-colors ${selectedProject?.id === p.id ? 'bg-yellow-50 border-l-4 border-l-yellow-500' : 'hover:bg-gray-100 border-l-4 border-l-transparent'}`}
            >
              <h3 className={`font-semibold ${selectedProject?.id === p.id ? 'text-yellow-900' : 'text-gray-800'}`}>{p.name}</h3>
              <p className="text-xs text-gray-500 truncate mt-1">{p.status}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Chat Area */}
      <div className="flex-1 flex flex-col bg-white">
        {selectedProject ? (
          <>
            <div className="p-4 border-b border-gray-200 bg-white">
              <h3 className="font-bold text-gray-800">{selectedProject.name} - Chat</h3>
              <p className="text-sm text-gray-500">Discuss tasks, delays, and resources.</p>
            </div>
            
            {/* Messages body */}
            <div className="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-4">
              {messages.map((m) => {
                const isMine = m.sender_id === user?.id;
                return (
                  <div key={m.id} className={`flex ${isMine ? 'justify-end' : 'justify-start'}`}>
                    <div className={`max-w-[70%] rounded-2xl px-4 py-2 ${isMine ? 'bg-slate-800 text-white rounded-br-none' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-none shadow-sm'}`}>
                      {!isMine && (
                        <div className="text-xs font-semibold mb-1 text-slate-500">{m.sender_name} <span className="opacity-75 uppercase tracking-wider text-[10px]">({m.sender_role})</span></div>
                      )}
                      <p className="text-sm leading-relaxed">{m.message}</p>
                      <div className={`text-[10px] mt-1 text-right ${isMine ? 'text-slate-300' : 'text-gray-400'}`}>
                        {new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                      </div>
                    </div>
                  </div>
                );
              })}
              {messages.length === 0 && (
                <div className="h-full flex items-center justify-center text-gray-400 italic">No messages yet. Start the conversation!</div>
              )}
              <div ref={messagesEndRef} />
            </div>

            {/* Message Input */}
            <form onSubmit={sendMessage} className="p-4 border-t border-gray-200 bg-white flex space-x-2">
              <input
                type="text"
                value={newMessage}
                onChange={(e) => setNewMessage(e.target.value)}
                placeholder="Type a message..."
                className="flex-1 p-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-yellow-500"
              />
              <button 
                type="submit" 
                disabled={!newMessage.trim()}
                className="bg-yellow-500 hover:bg-yellow-400 text-slate-900 p-3 rounded-lg flex items-center justify-center disabled:opacity-50 transition-colors"
              >
                <LucideSend className="w-5 h-5" />
              </button>
            </form>
          </>
        ) : (
          <div className="flex-1 flex items-center justify-center text-gray-500">
            Select a project to view messages
          </div>
        )}
      </div>
    </div>
  );
}
