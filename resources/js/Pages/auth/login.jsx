
import "./login.css";
import OFPPT from "../../assets/OFPPT2.webp";
import { useForm } from "@inertiajs/react";


export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: "",
  });
  const handleSubmit = async (e) => {

    e.preventDefault();
    // reset errors
    post("/login", {
      forceFormData: true,
    });

  };

  return (
    <div className="login-page">
      <form className="login-form" method="POST" onSubmit={handleSubmit}>
        <img
          src={OFPPT}
          alt="CHU Hassan II"
          className="w-[100px] mx-auto object-contain"
        />
        <h2 className="text-2xl font-bold mb-9 text-center">Welcome Back</h2>

        {/* Input email */}
        <div className="flex items-center my-2 bg-gray-900/5 border-gray-500/10 rounded gap-1 pl-2">
          <svg width="18" height="18" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="m2.5 4.375 3.875 2.906c.667.5 1.583.5 2.25 0L12.5 4.375" stroke="#6B7280" strokeOpacity=".6" strokeWidth="1.3" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M11.875 3.125h-8.75c-.69 0-1.25.56-1.25 1.25v6.25c0 .69.56 1.25 1.25 1.25h8.75c.69 0 1.25-.56 1.25-1.25v-6.25c0-.69-.56-1.25-1.25-1.25Z" stroke="#6B7280" strokeOpacity=".6" strokeWidth="1.3" strokeLinecap="round" />
          </svg>
          <input className="w-full text-black outline-none bg-transparent border-gray-500/10 py-3" name="email" type="email" value={data.email} onChange={e => setData('email', e.target.value)} placeholder="Email" required />
        </div>
        {errors.email && (
          <div className="text-red-500 text-sm">
            {errors.email}
          </div>
        )}

        {/* Input password */}
        <div className="flex items-center mt-2 mb-1 bg-gray-900/5 rounded gap-1 pl-2">
          <svg width="13" height="17" viewBox="0 0 13 17" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13 8.5c0-.938-.729-1.7-1.625-1.7h-.812V4.25C10.563 1.907 8.74 0 6.5 0S2.438 1.907 2.438 4.25V6.8h-.813C.729 6.8 0 7.562 0 8.5v6.8c0 .938.729 1.7 1.625 1.7h9.75c.896 0 1.625-.762 1.625-1.7zM4.063 4.25c0-1.406 1.093-2.55 2.437-2.55s2.438 1.144 2.438 2.55V6.8H4.061z" fill="#6B7280" />
          </svg>
          <input className="w-full outline-none bg-transparent border-gray-500/10 py-3" name="password" type="password" value={data.password} onChange={e => setData('password', e.target.value)} placeholder="Password" required />
        </div>
        {errors.password && (
          <div className="text-red-500 mb-4 text-sm">
            {errors.password}
          </div>
        )}

        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-1">
            <input id="checkbox" type="checkbox" className=" border-gray-900/10"/>
            <label htmlFor="checkbox">Remember me</label>
          </div>
          <a className="text-blue-600 underline" href="#">Forgot Password</a>
        </div>

        <button
          type="submit"
          className="w-full mb-3 bg-indigo-500 hover:bg-indigo-600/90 transition py-2.5 rounded text-white font-medium"
        >
          Log In
        </button>

        <div className="mt-8 text-center text-sm ">
          © 2026 OFPPT - Fès
        </div>
      </form>
    </div>
  )
}

