import React from 'react';

const Modal = ({ children, onClose }) => (
  <div className="modal-container">
    <div className="modal">
      <button onClick={onClose} className="modal-close-button">
        &times;
      </button>
      {children}
    </div>
  </div>
);

export default Modal;
