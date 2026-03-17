```javascript
import React, { useState } from 'https://esm.sh/react@18.2.0';

// LoginInput Component
// This component implements the "antigravity" floating label effect.

const LoginInput = ({ label, name, type = "text", icon, defaultValue = "", required = false }) => {
  const [isFocused, setIsFocused] = useState(false);
  const [value, setValue] = useState(defaultValue);
  
  // 1. Define the 'active' state
  // The label floats if the user clicks (isFocused) OR if there is text (value.length > 0)
  const isActive = isFocused || value.length > 0;

  // Handle change to update local state and ensure the underlying input value updates
  const handleChange = (e) => {
    setValue(e.target.value);
  };

  return (
    <div className={`relative border - 2 rounded - xl overflow - hidden transition - colors duration - 300 ${ isActive || isFocused ? 'border-indigo-500' : 'border-gray-400 dark:border-gray-600' } `}>
      
      {/* 2. The Icon (Collapsing Animation) */}
      {/* isActive ? width becomes 0 and opacity 0 : otherwise width 10 and visible */}
      <div 
        className={`
          absolute top - 0 left - 0 h - full
transition - all duration - 300 ease -in -out flex items - center justify - center
          ${ isActive ? 'w-0 opacity-0' : 'w-10 opacity-100' }
text - gray - 600 dark: text - gray - 400
    `}
      >
        {icon}
      </div>

      {/* 3. The Input Field (Padding Adjustment) */}
      {/* We add top padding (pt-5) when active so text doesn't hit the floating label */}
      <input
        name={name}
        type={type}
        value={value}
        onChange={handleChange}
        onFocus={() => setIsFocused(true)}
        onBlur={() => setIsFocused(false)}
        required={required}
        className={`
w - full h - 14 bg - transparent outline - none z - 10 text - gray - 900 dark: text - gray - 100
transition - all duration - 300 ease -in -out
          ${ isActive ? 'pt-5 pb-1 pl-3' : 'py-3 pl-10' }
`}
      />

      {/* 4. The Label (Floating/Antigravity Animation) */}
      {/* Moves from 'top-1/2' (center) to 'top-1' (top corner) */}
      <label
        className={`
          absolute pointer - events - none transition - all duration - 300 ease -in -out font - medium
          ${
    isActive
        ? 'top-1 left-3 text-[10px] text-indigo-500 font-bold' // Floated State
        : 'top-1/2 -translate-y-1/2 left-10 text-gray-600 dark:text-gray-400 text-sm'
} // Resting State
`}
      >
        {label}
      </label>
    </div>
  );
};

export default LoginInput;
```
