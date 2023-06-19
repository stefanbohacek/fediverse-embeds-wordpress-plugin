const dispatchEvent = (eventName) => {
  const event = new Event(eventName);
  document.dispatchEvent(event);
};

export { dispatchEvent };
