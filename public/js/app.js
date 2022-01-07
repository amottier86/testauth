import React from "react";

const App = (props) => {
  return <h1>Hello {props.name} !</h1>;
};

const element = React.createElement(<App2 />, { name: "Aurelien" });

React.render(element, document.getElementById("#root"));

class App2 extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      name: "UNKNOWN",
    };
  }

  updateName(name) {
    this.setState({ name: name });
  }

  render() {
    return (
      <h1 className="h1" onClick={this.updateName("Mario")}>
        Hello {this.props.name} !
      </h1>
    );
  }
}
