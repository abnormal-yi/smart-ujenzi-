import React, { useState } from 'react';
import { Navigate } from 'react-router-dom';
import { LucideHardHat, LucideGlobe } from 'lucide-react';

export default function Login({ onLogin, isAuthenticated }: { onLogin: (t: string) => void, isAuthenticated: boolean }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoginMode, setIsLoginMode] = useState(true);

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!isLoginMode) {
      setError('System is currently invite-only. Please contact the administrator.');
      return;
    }

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      
      if (!res.ok) {
        setError(data.error || 'Login failed');
      } else {
        onLogin(data.token);
      }
    } catch (err) {
      setError('Network error');
    }
  };

  return (
    <div className="min-h-screen bg-[#524B6B] flex items-center justify-center p-4 sm:p-8">
      <div className="flex w-full max-w-6xl h-[800px] overflow-hidden rounded-3xl shadow-2xl">
        
        {/* Left Side - Image & Branding */}
        <div className="hidden lg:flex flex-col w-1/2 relative bg-slate-900 overflow-hidden">
          <img 
            src="https://images.unsplash.com/photo-1541888081622-1de23f5b08e2?q=80&w=2670&auto=format&fit=crop"
            alt="Construction"
            referrerPolicy="no-referrer"
            className="absolute inset-0 w-full h-full object-cover opacity-80 pointer-events-none"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-[#0C0D10] via-transparent to-transparent opacity-90 pointer-events-none"></div>
          
          <div className="relative z-10 p-12 flex flex-col h-full justify-between">
            <div className="flex items-center space-x-3 text-white">
              <span className="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900">
                <LucideHardHat size={24} />
              </span>
              <span className="text-2xl font-bold tracking-wider">SMART UJENZI</span>
            </div>
            
            <div className="space-y-4">
              <div className="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-64">
                <div className="flex items-center text-white mb-2">
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex flex-col justify-center items-center mr-3">
                    <div className="w-4 h-4 bg-white rounded-full"></div>
                  </div>
                  <div>
                    <div className="font-bold">Tasks</div>
                    <div className="text-xs text-gray-300">Progress today</div>
                  </div>
                  <div className="ml-auto text-right">
                    <div className="font-bold">+12</div>
                    <div className="text-xs text-green-400">+15.2%</div>
                  </div>
                </div>
              </div>

              <div className="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-64 ml-8">
                <div className="flex items-center text-white">
                  <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">
                    MT
                  </div>
                  <div>
                    <div className="font-bold">Materials</div>
                    <div className="text-xs text-gray-300">Stock updates</div>
                  </div>
                  <div className="ml-auto text-right">
                    <div className="font-bold">+850</div>
                    <div className="text-xs text-green-400">+8.4%</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Right Side - Form */}
        <div className="w-full lg:w-1/2 bg-[#0C0D10] text-white flex flex-col p-8 sm:p-16 lg:px-24 justify-center relative">
          
          {/* Top right language toggle (mock) */}
          <div className="absolute top-8 right-8 hidden flex-none sm:flex items-center justify-center space-x-2 bg-white/5 border border-white/10 rounded-full px-4 py-2 cursor-pointer hover:bg-white/10 transition">
            <LucideGlobe size={16} className="text-gray-400" />
            <span className="text-sm font-medium pr-1">EN</span>
            <span className="text-xs text-gray-400 h-2 flex items-center">v</span>
          </div>

          <div className="max-w-md w-full mx-auto">
            <h2 className="text-4xl font-bold text-center mb-10">Welcome to SmartUjenzi!</h2>
            
            {/* Toggle Sign Up / Log In */}
            <div className="flex bg-[#1C1D21] rounded-xl p-1 mb-10">
              <button 
                type="button"
                onClick={() => setIsLoginMode(false)}
                className={`flex-1 py-3 text-sm font-bold rounded-lg transition-colors ${!isLoginMode ? 'bg-[#322A42] text-white shadow-sm' : 'text-gray-500 hover:text-white'}`}
              >
                SIGN UP
              </button>
              <button 
                type="button"
                onClick={() => setIsLoginMode(true)}
                className={`flex-1 py-3 text-sm font-bold rounded-lg transition-colors ${isLoginMode ? 'bg-[#322A42] text-white shadow-sm' : 'text-gray-500 hover:text-white'}`}
              >
                LOG IN
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              {error && (
                <div className="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center">
                  {error}
                </div>
              )}

              {!isLoginMode && (
                <>
                  <div className="relative">
                    <label className="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">
                      Name
                    </label>
                    <input
                      type="text"
                      className="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                      placeholder="John"
                    />
                  </div>
                  <div className="relative">
                    <label className="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">
                      Surname
                    </label>
                    <input
                      type="text"
                      className="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                      placeholder="Doe"
                    />
                  </div>
                </>
              )}

              <div className="relative">
                <label className="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">
                  Email (Login)
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                  placeholder="admin@example.com"
                  required
                />
              </div>

              <div className="relative">
                <label className="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">
                  Password must contain 8 characters or more
                </label>
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                  placeholder="********"
                  required
                />
              </div>

              {!isLoginMode && (
                <div className="flex items-center space-x-3 mt-4">
                  <input type="checkbox" id="terms" className="w-5 h-5 accent-[#20D08A] rounded border-gray-600 bg-transparent" />
                  <label htmlFor="terms" className="text-sm text-gray-300">
                    I have read and agree <a href="#" className="underline hover:text-white transition">Terms and Risk statements</a>
                  </label>
                </div>
              )}

              <button
                type="submit"
                className="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors mt-8"
              >
                {isLoginMode ? 'Log in' : 'Sign up'}
              </button>
              
              {isLoginMode && (
                <div className="text-center mt-6">
                  <p className="text-gray-400 text-sm leading-relaxed">
                    Demo Accounts:<br/>
                    <span className="text-gray-300">admin@example.com / admin123</span><br/>
                    <span className="text-gray-300">steve@example.com / pass123</span><br/>
                    <span className="text-gray-300">teleza@example.com / pass123</span>
                  </p>
                </div>
              )}
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}